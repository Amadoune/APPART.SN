<?php

namespace App\Providers;

use Appart\Modules\Notifications\Application\OwnerReader\Contract\NotificationOwnerReaderV1;
use Appart\Modules\Notifications\Application\OwnerReader\NotificationChannelOwnerReader;
use Appart\Modules\Notifications\Application\OwnerReader\NotificationOwnerReaderPolicy;
use Appart\Modules\Notifications\Application\OwnerReader\NotificationPreferenceOwnerReader;
use Appart\Modules\Notifications\Application\OwnerReader\NotificationTemplateOwnerReader;
use Appart\Modules\Notifications\Application\PublicRead\Contract\NotificationChannelReaderV1;
use Appart\Modules\Notifications\Application\PublicRead\Contract\NotificationPreferenceReaderV1;
use Appart\Modules\Notifications\Application\PublicRead\Contract\NotificationTemplateReaderV1;
use Illuminate\Support\ServiceProvider;

final class NotificationsOwnerReaderServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(NotificationOwnerReaderPolicy::class);
        $this->app->alias(NotificationOwnerReaderPolicy::class, NotificationOwnerReaderV1::class);
        $this->app->singleton(NotificationPreferenceOwnerReader::class);
        $this->app->alias(NotificationPreferenceOwnerReader::class, NotificationPreferenceReaderV1::class);
        $this->app->singleton(NotificationTemplateOwnerReader::class);
        $this->app->alias(NotificationTemplateOwnerReader::class, NotificationTemplateReaderV1::class);
        $this->app->singleton(NotificationChannelOwnerReader::class);
        $this->app->alias(NotificationChannelOwnerReader::class, NotificationChannelReaderV1::class);
    }
}
