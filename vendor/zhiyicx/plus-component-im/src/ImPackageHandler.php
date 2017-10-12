<?php

namespace Zhiyi\Component\ZhiyiPlus\PlusComponentIm;

use Zhiyi\Plus\Support\Configuration;
use Zhiyi\Plus\Support\PackageHandler;

class ImPackageHandler extends PackageHandler
{
    /**
     * The config store.
     *
     * @var \Zhiyi\Plus\Support\Configuration
     */
    protected $config;

    /**
     * Create the handler instance.
     *
     * @param \Zhiyi\Plus\Support\Configuration $conft
     * @author Seven Du <shiweidu@outlook.com>
     */
    public function __construct(Configuration $config)
    {
        $this->config = $config;
    }

    /**
     * Install handle.
     *
     * @param [type] $command
     * @return mixed
     * @author Seven Du <shiweidu@outlook.com>
     */
    public function installHandle($command)
    {
        if (config('im.open', false)) {
            $command->error('You have installed it.');

            return;
        }

        if (! $command->confirm('Are you sure you want to add')) {
            return;
        }

        // database up.
        include dirname(__DIR__).'/database/up.php';

        // publish asstes.
        $command->call('vendor:publish', [
            '--provider' => ImServiceProvider::class,
            '--tag' => 'public',
            '--force' => true,
        ]);

        $this->config->set('im.open', true);

        $command->info('Installed the IM component successfully.');
    }

    /**
     * remove handle.
     *
     * @param [type] $command
     * @return mixed
     * @author Seven Du <shiweidu@outlook.com>
     */
    public function removeHandle($command)
    {
        if (! config('im.open', false)) {
            $command->error('You have not installed yet.');

            return;
        }

        if (! $command->confirm('Is it removed')) {
            return;
        }

        // database down.
        include dirname(__DIR__).'/database/down.php';

        // close.
        $this->config->set('im.open', false);

        $command->info('success.');
    }
}
