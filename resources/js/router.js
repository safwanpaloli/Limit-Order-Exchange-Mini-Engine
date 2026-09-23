import { createRouter, createWebHistory } from 'vue-router';

import Login from './pages/Login.vue';
import Profile from './pages/Profile.vue';
import Trade from './pages/Trade.vue';

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
    {
        path: '/trade',
        name: 'Trade',
        component: Trade,
        meta: { requiresAuth: true }
    }
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
