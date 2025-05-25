<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Notification;
use App\Notifications\Channels\FcmChannel;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Notification::extend('fcm', function ($app) {
        //     return new class {
        //         public function send($notifiable, BaseNotification $notification)
        //         {
        //             if (method_exists($notification, 'toFcm')) {
        //                 $notification->toFcm($notifiable);
        //             }
        //         }
        //     };
        // });
        Notification::extend('fcm', function ($app) {
            return new FcmChannel();
        });
    }
}
