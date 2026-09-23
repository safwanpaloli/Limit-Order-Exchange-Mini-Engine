import { ref, computed, watch, Ref, ComputedRef } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { useToast } from './useToast';
import type { User, Asset, Order } from '../types';

/**
 * Composable for managing a user's wallet, assets, and active orders.
 */
export function useProfile() {
    const router = useRouter();
    const loading: Ref<boolean> = ref(true);
    const user: Ref<User | null> = ref(null);
    const assets: Ref<Asset[]> = ref([]);
    const orders: Ref<Order[]> = ref([]);
    const cancelling: Ref<number | null> = ref(null);
    const showCancelModal: Ref<boolean> = ref(false);
    const orderToCancel: Ref<Order | null> = ref(null);
    const filterSide: Ref<string> = ref('all');
    const filterStatus: Ref<string> = ref('all');
    const currentPage: Ref<number> = ref(1);
    const perPage: Ref<number> = ref(10);
    const totalPages: Ref<number> = ref(1);

    let isFetching = false;

    watch([filterSide, filterStatus, perPage], () => {
        currentPage.value = 1;
        if (!isFetching) fetchProfile();
    });

    watch(currentPage, () => {
        if (!isFetching) fetchProfile();
    });

    const nextPage = (): void => {
        if (currentPage.value < totalPages.value) currentPage.value++;
    };

    const prevPage = (): void => {
        if (currentPage.value > 1) currentPage.value--;
    };

    /**
     * Fetches the authenticated user's profile, including their wallet balances and active orders.
     */
    const fetchProfile = async (): Promise<number | void> => {
        isFetching = true;
        loading.value = true;
        try {
            const token = localStorage.getItem('auth_token');
            const res = await axios.get('/api/profile', {
                params: {
                    page: currentPage.value,
                    per_page: perPage.value,
                    side: filterSide.value,
                    status: filterStatus.value
                },
                headers: { 'Authorization': `Bearer ${token}` }
            });
            
            const data = res.data;
            user.value = data.user;
            assets.value = data.assets;
            
            // Laravel paginator object for orders
            orders.value = data.orders.data || [];
            totalPages.value = data.orders.last_page || 1;
            
            return data.user.id;
        } catch (e: any) {
            if (e.response && e.response.status === 401) {
                localStorage.removeItem('auth_token');
                router.push('/');
            } else {
                console.error('Failed to load profile', e);
            }
        } finally {
            loading.value = false;
            isFetching = false;
        }
    };

    /**
     * Subscribes to the user's specific private channel for real-time WebSocket updates.
     */
    const listenForWalletUpdates = (userId: number): void => {
        if ((window as any).Echo) {
            const token = localStorage.getItem('auth_token');
            if (token) {
                (window as any).Echo.connector.options.auth = {
                    headers: {
                        Authorization: `Bearer ${token}`
                    }
                };
                (window as any).Echo.connector.options.authEndpoint = '/api/broadcasting/auth';
            }

            (window as any).Echo.private(`user.${userId}`)
                .listen('WalletUpdated', () => {
                    fetchProfile();
                })
                .listen('OrderMatched', (e: any) => {
                    const { success } = useToast();
                    const trade = e.tradeData;
                    success(`Trade Filled: ${trade.side.toUpperCase()} ${parseFloat(trade.amount)} ${trade.symbol} @ $${parseFloat(trade.price)}`);
                    fetchProfile();
                });
        }
    };

    /**
     * Opens the order cancellation confirmation modal.
     * @param order The order that is targeted for cancellation.
     */
    const confirmCancel = (order: Order): void => {
        orderToCancel.value = order;
        showCancelModal.value = true;
    };

    /**
     * Executes the API request to cancel the targeted order.
     */
    const executeCancel = async (): Promise<void> => {
        const { success, error: toastError } = useToast();
        if (!orderToCancel.value) return;
        const id = orderToCancel.value.id;
        
        cancelling.value = id;
        try {
            const token = localStorage.getItem('auth_token');
            await axios.post(`/api/orders/${id}/cancel`, {}, {
                headers: { 'Authorization': `Bearer ${token}` }
            });
            
            success('Order cancelled successfully.');
            showCancelModal.value = false;
            orderToCancel.value = null;
            await fetchProfile();
        } catch (e: any) {
            toastError(e.response?.data?.message || 'Failed to cancel order.');
        } finally {
            cancelling.value = null;
        }
    };

    return {
        loading,
        user,
        assets,
        orders,
        filterSide,
        filterStatus,
        perPage,
        currentPage,
        totalPages,
        nextPage,
        prevPage,
        cancelling,
        showCancelModal,
        orderToCancel,
        fetchProfile,
        listenForWalletUpdates,
        confirmCancel,
        executeCancel
    };
}
