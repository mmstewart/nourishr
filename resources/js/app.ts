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
    const isAuthenticated = !!event.detail.page.props.auth?.user;
    const component = event.detail.page.component;

    const components = ['auth/Setup', 'auth/Login', 'auth/Register', 'Welcome', 'Privacy', 'Contact'];

    // Force light mode on setup pages regardless of auth
    if (components.some((c) => component.includes(c))) {
        initializeTheme(false);
        return;
    }

    initializeTheme(isAuthenticated);
});
