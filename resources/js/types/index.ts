export interface User {
    id: number;
    name: string;
    email: string;
    balance: number | string;
}

export interface Asset {
    id: number;
    symbol: string;
    amount: number | string;
    locked_amount: number | string;
}

export interface Order {
    id: number;
    side: 'buy' | 'sell';
    symbol: string;
    price: number | string;
    amount: number | string;
    status: number;
}

export interface OrderItem {
    id: number;
    price: string | number;
    amount: string | number;
}

export interface Orderbook {
    buy: OrderItem[];
    sell: OrderItem[];
}
