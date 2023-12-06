<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Storage;

class GoogleDriveServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        try {
            app()->config['filesystems.disks.google'] = [
                'driver' => 'google',
                'clientId' => env('GOOGLE_DRIVE_CLIENT_ID'),
                'clientSecret' => env('GOOGLE_DRIVE_CLIENT_SECRET'),
                'refreshToken' => env('GOOGLE_DRIVE_REFRESH_TOKEN'),
                'folder' => env('GOOGLE_DRIVE_FOLDER'), // without folder is root of drive or team drive
                //'teamDriveId' => env('GOOGLE_DRIVE_TEAM_DRIVE_ID'),
            ];

            Storage::extend('google', function ($app, $config) {
                $options = [];

                if (! empty($config['teamDriveId'] ?? null)) {
                    $options['teamDriveId'] = $config['teamDriveId'];
                }

                $client = new \Google\Client();
                $client->setClientId($config['clientId']);
                $client->setClientSecret($config['clientSecret']);
                $client->refreshToken($config['refreshToken']);

                $service = new \Google\Service\Drive($client);
                $adapter = new \Masbug\Flysystem\GoogleDriveAdapter($service, $config['folder'] ?? '/', $options);
                $driver = new \League\Flysystem\Filesystem($adapter);

                return new \Illuminate\Filesystem\FilesystemAdapter($driver, $adapter);
            });
        } catch(\Exception $e) {
            Log::error($e);
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
