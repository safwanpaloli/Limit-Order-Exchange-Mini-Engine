import { createRouter, createWebHistory } from 'vue-router';

import Login from './pages/Login.vue';
import Profile from './pages/Profile.vue';

const routes = [
    {
        path: '/',
        name: 'Login',
        component: Login,
        meta: { guestOnly: true }
    },
    {
        path: '/profile',
        name: 'Profile',
        component: Profile,
        meta: { requiresAuth: true }
    },
    // We'll add the Trade route in Phase 3
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach((to, from, next) => {
    const token = localStorage.getItem('auth_token');

    if (to.meta.requiresAuth && !token) {
        next({ name: 'Login' });
    } else if (to.meta.guestOnly && token) {
        next({ name: 'Profile' });
    } else {
        next();
    }
});

export default router;
