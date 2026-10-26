/**
 * APP.JS
 *
 * Compiled via vite.config.js. Components live in ./components/, one file each,
 * exporting a mount function:
 *
 *   import {mountThing} from './components/thing.js';
 *
**/

import '../scss/app.scss';

const ready = () => {
    document.documentElement.classList.remove('no-js', 'is-loading');
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', ready);
} else {
    ready();
}

if (import.meta.hot) {
    import.meta.hot.accept();
}
