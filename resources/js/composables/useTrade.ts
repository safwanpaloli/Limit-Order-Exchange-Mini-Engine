import { ref, computed, Ref, ComputedRef } from 'vue';
import axios from 'axios';
import type { Orderbook } from '../types';

/**
 * Composable for managing the trade terminal state, including the orderbook and limit order execution.
 */
export function useTrade() {
    const side: Ref<'buy' | 'sell'> = ref('buy');
    const price: Ref<number> = ref(0);
    const amount: Ref<number> = ref(0);
    const submitting: Ref<boolean> = ref(false);
    const error: Ref<string> = ref('');
    const success: Ref<string> = ref('');
    const showModal: Ref<boolean> = ref(false);
    const orderbook: Ref<Orderbook> = ref({ buy: [], sell: [] });
    const loadingOrderbook: Ref<boolean> = ref(true);

    /**
     * Calculates the total fiat value of the current order form inputs.
     */
    const totalValue: ComputedRef<number> = computed(() => {
        return (price.value || 0) * (amount.value || 0);
    });

    /**
     * Fetches the active market orderbook for the given symbol (defaults to BTC).
     */
    const fetchOrderbook = async (): Promise<void> => {
        loadingOrderbook.value = true;
        try {
            const token = localStorage.getItem('auth_token');
            const res = await axios.get('/api/orders?symbol=BTC', {
                headers: { 'Authorization': `Bearer ${token}` }
            });
            orderbook.value = res.data;
        } catch (e) {
            console.error('Failed to load orderbook', e);
        } finally {
            loadingOrderbook.value = false;
        }
    };

    /**
     * Subscribes to the public orderbook channel for real-time WebSocket updates.
     */
    const listenForOrderbookUpdates = (symbol: string = 'BTC'): void => {
        if ((window as any).Echo) {
            (window as any).Echo.channel(`orderbook.${symbol}`)
                .listen('OrderbookUpdated', () => {
                    fetchOrderbook();
                });
        }
    };

    /**
     * Validates the order form inputs and opens the confirmation modal if valid.
     */
    const confirmOrder = (): void => {
        error.value = '';
        success.value = '';
        if (price.value > 0 && amount.value > 0) {
            showModal.value = true;
        }
    };

    /**
     * Submits the limit order to the backend API and refreshes the orderbook upon success.
     */
    const executeOrder = async (): Promise<void> => {
        submitting.value = true;
        error.value = '';
        success.value = '';
        
        try {
            const token = localStorage.getItem('auth_token');
            await axios.post('/api/orders', {
                symbol: 'BTC',
                side: side.value,
                price: price.value,
                amount: amount.value
            }, {
                headers: { 'Authorization': `Bearer ${token}` }
            });
            
            success.value = 'Order placed successfully!';
            price.value = 0;
            amount.value = 0;
            showModal.value = false;
            
            fetchOrderbook();
            
            setTimeout(() => { success.value = ''; }, 3000);
        } catch (e: any) {
            showModal.value = false;
            error.value = e.response?.data?.message || 'Failed to place order.';
        } finally {
            submitting.value = false;
        }
    };

    return {
        side,
        price,
        amount,
        submitting,
        error,
        success,
        showModal,
        orderbook,
        loadingOrderbook,
        totalValue,
        fetchOrderbook,
        listenForOrderbookUpdates,
        confirmOrder,
        executeOrder
    };
}
