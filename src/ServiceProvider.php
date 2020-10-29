<?php

namespace Lym125\SendCloud;

use GuzzleHttp\Client as HttpClient;
use Illuminate\Mail\MailManager;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Lym125\SendCloud\Transport\SendCloudTransport;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        config([
            'mail.mailers.sendcloud' => array_merge([
                'transport' => 'sendcloud',
            ], config('mail.mailers.sendcloud', [])),
        ]);

        $this->mergeConfigFrom(
            __DIR__.'/../config/services.php',
            'services'
        );

        $this->registerSendCloudTransport();
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/services.php' => config_path('services.php'),
            ]);
        }
    }

    /**
     * Register the SendCloud Swift Transport driver.
     *
     * @return void
     */
    protected function registerSendCloudTransport()
    {
        $this->app->afterResolving('mail.manager', function (MailManager $mailManager) {
            $mailManager->extend('sendcloud', function ($config) {
                if (! isset($config['user'])) {
                    $config = $this->app['config']->get('services.sendcloud', []);
                }

                $guzzleClient = new HttpClient(
                    array_merge($config['guzzle'] ?? [], [
                        'connect_timeout' => 60,
                    ])
                );

                return new SendCloudTransport(
                    $guzzleClient,
                    $config['key'],
                    $config['user'],
                    $config['endpoint']
                );
            });
        });
    }
}
