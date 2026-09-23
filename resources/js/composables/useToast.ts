import { ref } from 'vue';

type ToastType = 'success' | 'error' | 'info';

interface Toast {
    id: number;
    message: string;
    type: ToastType;
}

const toasts = ref<Toast[]>([]);
let nextId = 0;

export function useToast() {
    const addToast = (message: string, type: ToastType = 'info') => {
        const id = nextId++;
        toasts.value.push({ id, message, type });
        setTimeout(() => removeToast(id), 5000);
    };

    const removeToast = (id: number) => {
        toasts.value = toasts.value.filter(t => t.id !== id);
    };

    const success = (msg: string) => addToast(msg, 'success');
    const error = (msg: string) => addToast(msg, 'error');
    const info = (msg: string) => addToast(msg, 'info');

    return { toasts, success, error, info, removeToast };
}
