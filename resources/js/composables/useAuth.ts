import { ref, Ref } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

/**
 * Global authentication state to share across components.
 */
const email: Ref<string> = ref('');
const password: Ref<string> = ref('');
const showPassword: Ref<boolean> = ref(false);
const error: Ref<string> = ref('');
const loading: Ref<boolean> = ref(false);
const isLoggedIn: Ref<boolean> = ref(!!localStorage.getItem('auth_token'));

/**
 * Composable for managing user authentication state and logic.
 */
export function useAuth() {
    const router = useRouter();

    /**
     * Checks if the user is currently authenticated based on the presence of an auth token.
     */
    const checkAuth = (): void => {
        isLoggedIn.value = !!localStorage.getItem('auth_token');
    };

    /**
     * Handles the login form submission.
     */
    const handleLogin = async (): Promise<void> => {
        loading.value = true;
        error.value = '';
        try {
            const res = await axios.post('/api/login', {
                email: email.value,
                password: password.value
            });
            
            const data = res.data;
            localStorage.setItem('auth_token', data.access_token);
            isLoggedIn.value = true;
            router.push('/profile');
        } catch (e: any) {
            if (e.response && e.response.data && e.response.data.message) {
                error.value = e.response.data.message;
            } else {
                error.value = 'An error occurred';
            }
        } finally {
            loading.value = false;
        }
    };

    /**
     * Handles user logout by revoking the token on the server and clearing local state.
     */
    const logout = async (): Promise<void> => {
        try {
            const token = localStorage.getItem('auth_token');
            if (token) {
                await axios.post('/api/logout', {}, {
                    headers: { 'Authorization': `Bearer ${token}` }
                });
            }
        } catch (e) {
            // ignore
        } finally {
            localStorage.removeItem('auth_token');
            isLoggedIn.value = false;
            router.push('/');
        }
    };

    return {
        email,
        password,
        showPassword,
        error,
        loading,
        isLoggedIn,
        checkAuth,
        handleLogin,
        logout
    };
}
