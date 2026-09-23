import { ref, computed, watch, Ref, ComputedRef } from 'vue';
import axios from 'axios';
import { useToast } from './useToast';
import type { Orderbook } from '../types';

/**
 * Composable for managing the trade terminal state, including the orderbook and limit order execution.
 */
export function useTrade() {
    const symbol: Ref<string> = ref('BTC');
    const side: Ref<'buy' | 'sell'> = ref('buy');
    const price: Ref<number> = ref(0);
    const amount: Ref<number> = ref(0);
    const submitting: Ref<boolean> = ref(false);
    const error: Ref<string> = ref('');
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
     * Computes the orderbook with cumulative volume for depth preview.
     */
    const orderbookWithCumulative = computed(() => {
        let buyCum = 0;
        const buy = orderbook.value.buy.map(o => {
            buyCum += parseFloat(o.amount as unknown as string);
            return { ...o, cumulative: buyCum };
        });

        let sellCum = 0;
        // Asks are usually accumulated from lowest price (which is at the end if sorted desc)
        // For simplicity, we accumulate top to bottom.
        const sell = orderbook.value.sell.map(o => {
            sellCum += parseFloat(o.amount as unknown as string);
            return { ...o, cumulative: sellCum };
        });

        return { buy, sell };
    });

    /**
     * Fetches the active market orderbook for the given symbol (defaults to BTC).
     */
    const fetchOrderbook = async (): Promise<void> => {
        loadingOrderbook.value = true;
        try {
            const token = localStorage.getItem('auth_token');
            const res = await axios.get(`/api/orders?symbol=${symbol.value}`, {
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
    let currentChannel: string | null = null;
    
    const listenForOrderbookUpdates = (): void => {
        if ((window as any).Echo) {
            if (currentChannel) {
                (window as any).Echo.leave(currentChannel);
            }
            
            currentChannel = `orderbook.${symbol.value}`;
            (window as any).Echo.channel(currentChannel)
                .listen('OrderbookUpdated', () => {
                    fetchOrderbook();
                });
        }
    };

    watch(symbol, () => {
        fetchOrderbook();
        listenForOrderbookUpdates();
    });

    /**
     * Validates the order form inputs and opens the confirmation modal if valid.
     */
    const confirmOrder = (): void => {
        error.value = '';
        if (price.value > 0 && amount.value > 0) {
            showModal.value = true;
        }
    };

    /**
     * Submits the limit order to the backend API and refreshes the orderbook upon success.
     */
    const executeOrder = async (): Promise<void> => {
        const { success, error: toastError } = useToast();
        submitting.value = true;
        error.value = '';
        
        try {
            const token = localStorage.getItem('auth_token');
            await axios.post('/api/orders', {
                symbol: symbol.value,
                side: side.value,
                price: price.value,
                amount: amount.value
            }, {
                headers: { 'Authorization': `Bearer ${token}` }
            });
            
            success('Order placed successfully!');
            price.value = 0;
            amount.value = 0;
            showModal.value = false;
            
            fetchOrderbook();
        } catch (e: any) {
            showModal.value = false;
            toastError(e.response?.data?.message || 'Failed to place order.');
        } finally {
            submitting.value = false;
        }
    };

    return {
        symbol,
        side,
        price,
        amount,
        submitting,
        error,
        showModal,
        orderbook,
        orderbookWithCumulative,
        loadingOrderbook,
        totalValue,
        fetchOrderbook,
        listenForOrderbookUpdates,
        confirmOrder,
        executeOrder
    };
}
