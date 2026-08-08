<?php

namespace App\Providers;

use App\Http\Controllers\NotificationChannelController;
use App\Http\Controllers\NotificationPreferenceController;
use App\Http\Controllers\NotificationTemplateController;
use App\Http\Notifications\NotificationsResponseFactory;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

final class NotificationsHttpServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(NotificationsResponseFactory::class);
        $this->app->singleton(NotificationPreferenceController::class);
        $this->app->singleton(NotificationTemplateController::class);
        $this->app->singleton(NotificationChannelController::class);
    }

    public function boot(): void
    {
        Route::get('/api/notifications/preference', NotificationPreferenceController::class)->name('notifications.preference');
        Route::get('/api/notifications/template', NotificationTemplateController::class)->name('notifications.template');
        Route::get('/api/notifications/channel', NotificationChannelController::class)->name('notifications.channel');
    }
}
