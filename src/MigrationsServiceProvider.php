<?php

namespace Luminee\Migrations;

use Illuminate\Filesystem\Filesystem;
use Luminee\Migrations\Console\Commands;
use Illuminate\Support\ServiceProvider;

class MigrationsServiceProvider extends ServiceProvider
{
    /**
     * @var string
     */
    protected $config = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR;
    protected $src = __DIR__ . DIRECTORY_SEPARATOR;

    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([$this->config . 'migrations.php' => config_path('migrations.php')]);
        $this->publishes([$this->src . 'chariot' => base_path('chariot')]);
        $this->publishes([$this->src . '.conn.conf.php.example' => base_path('.conn.conf.php.example')]);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        if (file_exists($this->config . 'migrations.php'))
            $this->mergeConfigFrom($this->config . 'migrations.php', 'migrations');

        $this->app->singleton('lum.mig.command.mk:module:migration', function ($app) {
            return new Commands\MakeModuleMigration($app->make(Filesystem::class));
        });
        $this->app->singleton('lum.mig.command.mk:module:seeder', function ($app) {
            return new Commands\MakeModuleSeeder($app->make(Filesystem::class));
        });
        $this->app->singleton('lum.mig.command.mk:module:script', function ($app) {
            return new Commands\MakeModuleScript($app->make(Filesystem::class));
        });
        $this->app->singleton('lum.mig.command.mk:module:model', function ($app) {
            return new Commands\MakeModuleModel($app->make(Filesystem::class));
        });
        $this->app->singleton('lum.mig.command.module:migrate', function () {
            return new Commands\ModuleMigrate();
        });
        $this->app->singleton('lum.mig.command.module:seed', function () {
            return new Commands\ModuleSeed();
        });

        $this->commands('lum.mig.command.mk:module:migration');
        $this->commands('lum.mig.command.mk:module:seeder');
        $this->commands('lum.mig.command.mk:module:script');
        $this->commands('lum.mig.command.mk:module:model');
        $this->commands('lum.mig.command.module:migrate');
        $this->commands('lum.mig.command.module:seed');
    }

}