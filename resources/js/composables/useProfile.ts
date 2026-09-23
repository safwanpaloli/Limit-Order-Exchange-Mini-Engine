import { ref, Ref } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
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

    /**
     * Fetches the authenticated user's profile, including their wallet balances and active orders.
     */
    const fetchProfile = async (): Promise<void> => {
        loading.value = true;
        try {
            const token = localStorage.getItem('auth_token');
            const res = await axios.get('/api/profile', {
                headers: { 'Authorization': `Bearer ${token}` }
            });
            
            const data = res.data;
            user.value = data.user;
            assets.value = data.assets;
            orders.value = data.orders || [];
        } catch (e: any) {
            if (e.response && e.response.status === 401) {
                localStorage.removeItem('auth_token');
                router.push('/');
            } else {
                console.error('Failed to load profile', e);
            }
        } finally {
            loading.value = false;
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
        if (!orderToCancel.value) return;
        const id = orderToCancel.value.id;
        
        cancelling.value = id;
        try {
            const token = localStorage.getItem('auth_token');
            await axios.post(`/api/orders/${id}/cancel`, {}, {
                headers: { 'Authorization': `Bearer ${token}` }
            });
            
            showCancelModal.value = false;
            orderToCancel.value = null;
            await fetchProfile();
        } catch (e: any) {
            alert(e.response?.data?.message || 'Failed to cancel order.');
        } finally {
            cancelling.value = null;
        }
    };

    return {
        loading,
        user,
        assets,
        orders,
        cancelling,
        showCancelModal,
        orderToCancel,
        fetchProfile,
        confirmCancel,
        executeCancel
    };
}
