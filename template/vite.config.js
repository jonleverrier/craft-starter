import {defineConfig} from 'vite';
import path from 'path';
import fs from 'fs';
import ViteRestart from 'vite-plugin-restart';
import viteCompression from 'vite-plugin-compression';
import terser from '@rollup/plugin-terser';
import VitePluginSvgSpritemap from '@spiriit/vite-plugin-svg-spritemap';
import critical from 'rollup-plugin-critical';

// Critical CSS: one LIVE url per view template. `template` must be the view's path
// without .twig — Craft looks the file up by it. A url that 404s captures the 404 page.
const CRITICAL_PAGES = [
    {uri: '', template: '_views/single/home/default'},
    // A url that 404s on purpose: the error view is the page to capture.
    {uri: '__critical-css-404', template: '_views/error'},
];

// A view with no CRITICAL_PAGES entry paints unstyled on a first visit, then jumps.
// Warn about any at build time. Partials (_name) aren't pages.
const NOT_PAGES = /\/_[^/]+$/;
function criticalCoverage(pages) {
    return {
        name: 'critical-coverage',
        buildStart() {
            const root = path.resolve(__dirname, 'craft/templates');
            const walk = (dir) => fs.readdirSync(dir, {withFileTypes: true}).flatMap((d) =>
                d.isDirectory() ? walk(path.join(dir, d.name)) : d.name.endsWith('.twig') ? [path.join(dir, d.name)] : []);
            const covered = new Set(pages.map((p) => p.template));
            const missing = walk(path.join(root, '_views'))
                .map((f) => path.relative(root, f).replace(/\.twig$/, ''))
                .filter((t) => !NOT_PAGES.test(t) && !covered.has(t));
            if (missing.length) {
                this.warn(`No critical CSS for: ${missing.join(', ')}. Add a live URL for each to CRITICAL_PAGES in vite.config.js, or they paint unstyled.`);
            }
        },
    };
}

// https://vitejs.dev/config/
export default defineConfig(({command}) => ({
    base: command === 'serve' ? '' : '/dist/',
    publicDir: path.resolve(__dirname, './build/static'),
    build: {
        emptyOutDir: true,
        manifest: 'manifest.json',
        outDir: path.resolve('./public/dist'),
        sourcemap: false,
        assetsInlineLimit: 0,
        rollupOptions: {
            input: {
                'js/app': './build/js/app.js',
                // @font-face rules, inlined in every page's head (see build/scss/fonts.scss).
                'fonts': './build/scss/fonts.scss',
            },
            output: {
                entryFileNames: '[name].[hash].js',
                chunkFileNames: '[name].[hash].js',
                assetFileNames: ({name}) => {
                    if (/\.(gif|jpe?g|png|svg|webp|ico)$/.test(name ?? '')) {
                        return 'img/[name].[hash][extname]';
                    }
                    if (/\.(woff|woff2|otf|ttf)$/.test(name ?? '')) {
                        return 'fonts/[name].[hash][extname]';
                    }
                    if (/\.css$/.test(name ?? '')) {
                        return 'css/[name].[hash][extname]';
                    }
                    return 'assets/[name].[hash][extname]';
                },
            },
        },
    },
    css: {
        preprocessorOptions: {
            scss: {
                additionalData: command === 'serve'
                    ? `@use '@css/theme/setup' with ($sprite-type: uri);`
                    : `@use '@css/theme/setup' with ($sprite-type: fragment);`,
            },
        },
    },
    plugins: [
        criticalCoverage(CRITICAL_PAGES),
        VitePluginSvgSpritemap('./build/icons/*.svg', {
            output: {
                filename: '[name].[hash].[ext]',
            },
            styles: {
                lang: 'scss',
                filename: 'build/scss/templates/_sprite.scss',
            },
            svgo: {
                plugins: [
                    {
                        name: 'removeAttrs',
                        params: {
                            attrs: ['clip-path', 'mask']
                        }
                    }
                ]
            },
        }),
        viteCompression(),
        terser({
            format: {
                comments: false,
            },
        }),
        // Critical CSS, build only. Renders each CRITICAL_PAGES url of the LIVE site in
        // headless Chromium (.ddev/web-build/Dockerfile.chromium), so the site must be up.
        // SKIP_CRITICAL=1 skips it for one build when something is broken — don't leave it set:
        // pages then paint unstyled and jump when the stylesheet lands.
        ...(process.env.SKIP_CRITICAL === '1' ? [] : [critical({
            criticalUrl: process.env.URL,
            criticalBase: './public/dist/criticalcss/',
            criticalPages: CRITICAL_PAGES,
            criticalConfig: {
                // One capture per breakpoint (48/64/80/108em), not the plugin's single 1200x1200.
                dimensions: [
                    {width: 390, height: 844},
                    {width: 768, height: 1024},
                    {width: 1280, height: 900},
                    {width: 1728, height: 1000},
                ],
                // Write .css files; Craft inlines them itself (craft.vite.includeCriticalCssTags).
                inline: false,
                extract: false,
                // @font-face lives in fonts.scss, inlined separately. Never twice.
                ignore: {atrule: ['@font-face']},
                // DDEV's certificate is self-signed.
                request: {
                    https: {rejectUnauthorized: false},
                },
            },
        })]),
        ViteRestart({
            reload: [
                'craft/templates/**/*',
                'build/icons/**/*',
            ],
            restart: [
                'vite.config.js',
                'craft/config/vite.php',
            ]
        }),
    ],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'build'),
            '@css': path.resolve(__dirname, 'build/scss'),
            '@js': path.resolve(__dirname, 'build/js'),
            '@fonts': path.resolve(__dirname, 'build/fonts'),
            '@images': path.resolve(__dirname, 'build/images'),
        },
    },
    server: {
        fs: {
            strict: false
        },
        cors: true,
        host: '0.0.0.0',
        origin: process.env.URL.substring(0, process.env.URL.length - 1) + ':3000',
        port: 3000,
        strictPort: true,
    },
}));
