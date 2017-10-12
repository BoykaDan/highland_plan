<?php

namespace Zhiyi\Component\ZhiyiPlus\PlusComponentIm;

use Zhiyi\Plus\Support\PackageHandler;
use Illuminate\Support\ServiceProvider;
use Zhiyi\Plus\Support\ManageRepository;

class ImServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the provider.
     *
     * @return void
     * @author Seven Du <shiweidu@outlook.com>
     */
    public function boot()
    {
        // Load routes.
        $this->loadRoutesFrom(
            dirname(__DIR__).'/routes.php'
        );

        // Load views.
        $this->loadViewsFrom(dirname(__DIR__).'/views/', 'component-im');

        // publish asstes
        $this->publishes([
            dirname(__DIR__).'/asstes' => $this->app->publicPath().'/zhiyi/im',
        ], 'public');

        // Register handler.
        PackageHandler::loadHandleFrom('im', ImPackageHandler::class);
    }

    /**
     * Register the provider.
     *
     * @return void
     * @author Seven Du <shiweidu@outlook.com>
     */
    public function register()
    {
        if ($this->app->config['im.open']) {
            $this->app->make(ManageRepository::class)->loadManageFrom('即时通讯', 'im:admin', [
                'route' => true,
                'icon' => 'IM',
            ]);
        }
    }
}
