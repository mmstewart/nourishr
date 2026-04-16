import '../css/app.css';

import { router, createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h, type DefineComponent } from 'vue';
import { initializeTheme } from './composables/useAppearance';

const appName = import.meta.env.VITE_APP_NAME || 'Nourishr';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) =>
        resolvePageComponent(
            `./pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);

        const isAuthenticated = !!props.initialPage.props.auth?.user;

        initializeTheme(isAuthenticated);
    },
    progress: {
        color: '#00c951',
    },
});

// re-runs theme initialization on every Inertia navigation so login/logout transitions apply the correct theme without a full refresh
router.on('navigate', (event) => {
    const { props, component } = event.detail.page;
    const isAuthenticated = !!props.auth?.user;

    const alwaysLight = new Set(['Welcome', 'Privacy', 'Terms', 'auth/Login', 'auth/Register', 'auth/ForgotPassword', 'auth/ResetPassword', 'auth/TwoFactorChallenge']);

    const guestOnlyLight = new Set(['Contact', 'auth/Setup', 'auth/VerifyEmail']);

    let shouldBeDark = isAuthenticated;

    if (alwaysLight.has(component)) {
        shouldBeDark = false;
    } else if (guestOnlyLight.has(component) && !isAuthenticated) {
        shouldBeDark = false;
    }

    initializeTheme(shouldBeDark);
});
