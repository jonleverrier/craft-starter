<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /** Where `npm run dev` listens, from inside the web container (.ddev/docker-compose.vite.yaml). */
    private const DEV_SERVER_PORT = 3000;

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // vite.config.js builds into public/dist (laravel-vite-plugin's buildDirectory).
        Vite::useBuildDirectory('dist');

        // laravel-vite-plugin writes public/hot while the dev server runs and removes it on a
        // clean exit. A killed server (or `ddev stop` mid-dev) leaves it behind, and every page
        // then loads its CSS and JS from a server that isn't there. So, locally, only believe
        // the file if something is listening — what craft-vite's checkDevServer used to do.
        if ($this->app->isLocal() && is_file(public_path('hot')) && !$this->devServerRunning()) {
            Vite::useHotFile(storage_path('framework/vite.hot'));
        }
    }

    private function devServerRunning(): bool
    {
        $socket = @fsockopen('127.0.0.1', self::DEV_SERVER_PORT, $errno, $errstr, 0.2);

        if ($socket === false) {
            return false;
        }

        fclose($socket);

        return true;
    }
}
