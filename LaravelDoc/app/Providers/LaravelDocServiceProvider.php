<?php

namespace Plugins\LaravelDoc\Providers;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Symfony\Component\Finder\Finder;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Plugins\LaravelDoc\Http\Controllers\OpenapiController;

class LaravelDocServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(dirname(__DIR__, 2) . '/config/yapi.php', 'yapi');

        $this->publishes([
            dirname(__DIR__, 2) . '/config/yapi.php' => config_path('yapi.php'),
        ], 'laravel-doc-config');

        $this->publishes([
            dirname(__DIR__, 2) . '/stubs/Tests/' => base_path('tests'),
        ], 'laravel-doc-yapi');

        $this->registerRoute();
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $commandsDirectory = dirname(__DIR__) . '/Console/Commands';
        if (File::exists($commandsDirectory)) {
            $this->load($commandsDirectory);
        }
    }

    public function registerRoute()
    {
        if (!config('yapi.openapi.enable', true)) {
            return;
        }

        if (config('yapi.openapi.route.enable', true)) {
            Route::middleware(config('yapi.openapi.route.middleware', []))
                ->any(config('yapi.openapi.route.path', 'openapi'), [OpenapiController::class, 'show']);
        }
    }

    /**
     * Register all of the commands in the given directory.
     *
     * @param array|string $paths
     */
    protected function load($paths): void
    {
        $paths = array_unique(Arr::wrap($paths));

        $paths = array_filter($paths, function ($path) {
            return is_dir($path);
        });

        if (empty($paths)) {
            return;
        }

        $commands = [];
        foreach ((new Finder)->in($paths)->files() as $command) {
            $commandClass = Str::before(self::class, 'Providers\\') . 'Console\\Commands\\' . str_replace('.php', '', $command->getBasename());
            if (class_exists($commandClass)) {
                $commands[] = $commandClass;
            }
        }

        $this->commands($commands);
    }
}
