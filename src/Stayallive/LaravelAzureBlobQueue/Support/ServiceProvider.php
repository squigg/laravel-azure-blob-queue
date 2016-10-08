<?php

namespace Stayallive\LaravelAzureBlobQueue\Support;

use Illuminate\Queue\QueueManager;
use Stayallive\LaravelAzureBlobQueue\AzureConnector;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->booted(function () {

            /** @var QueueManager $manager */
            $manager = $this->app['queue'];

            $manager->addConnector('azure', function () {
                return new AzureConnector;
            });
        });
    }
}
