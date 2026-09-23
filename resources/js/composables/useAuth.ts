import { ref, Ref } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

/**
 * Global authentication state to share across components.
 */
const name: Ref<string> = ref('');
const email: Ref<string> = ref('');
const password: Ref<string> = ref('');
const password_confirmation: Ref<string> = ref('');
const showPassword: Ref<boolean> = ref(false);
const showConfirmPassword: Ref<boolean> = ref(false);
const isRegistering: Ref<boolean> = ref(false);
const error: Ref<string> = ref('');
const loading: Ref<boolean> = ref(false);
const isLoggedIn: Ref<boolean> = ref(!!localStorage.getItem('auth_token'));

/**
 * Composable for managing user authentication state and logic.
 */
export function useAuth() {
    const router = useRouter();

    /**
     * Clears all authentication form fields.
     */
    const resetFields = (): void => {
        name.value = '';
        email.value = '';
        password.value = '';
        password_confirmation.value = '';
        error.value = '';
    };

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
            resetFields();
            router.push('/profile');
        } catch (e: any) {
            if (e.response && e.response.data && e.response.data.errors) {
                const firstErrorKey = Object.keys(e.response.data.errors)[0];
                error.value = e.response.data.errors[firstErrorKey][0];
            } else if (e.response && e.response.data && e.response.data.message) {
                error.value = e.response.data.message;
            } else {
                error.value = 'An error occurred';
            }
        } finally {
            loading.value = false;
        }
    };

    /**
     * Handles the registration form submission.
     */
    const handleRegister = async (): Promise<void> => {
        loading.value = true;
        error.value = '';
        try {
            const res = await axios.post('/api/register', {
                name: name.value,
                email: email.value,
                password: password.value,
                password_confirmation: password_confirmation.value
            });
            
            const data = res.data;
            localStorage.setItem('auth_token', data.access_token);
            isLoggedIn.value = true;
            resetFields();
            router.push('/profile');
        } catch (e: any) {
            if (e.response && e.response.data && e.response.data.errors) {
                const firstErrorKey = Object.keys(e.response.data.errors)[0];
                error.value = e.response.data.errors[firstErrorKey][0];
            } else if (e.response && e.response.data && e.response.data.message) {
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
            resetFields();
            router.push('/');
        }
    };

    return {
        name,
        email,
        password,
        password_confirmation,
        showPassword,
        showConfirmPassword,
        isRegistering,
        error,
        loading,
        isLoggedIn,
        checkAuth,
        handleLogin,
        handleRegister,
        logout
    };
}
