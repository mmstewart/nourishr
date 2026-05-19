import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';

export default defineConfig(({ command }) => {
    const plugins = [
        laravel({
            input: ['resources/js/app.ts'],
            ssr: 'resources/js/ssr.ts',
            refresh: true,
        }),
        tailwindcss(),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ];

    if (command === 'serve') {
        const { wayfinder } = await import('@laravel/vite-plugin-wayfinder');

        plugins.push(
            wayfinder({
                formVariants: true,
            }),
        );
    }

    return {
        plugins: plugins,
    };
});
