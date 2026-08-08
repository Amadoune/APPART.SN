<?php

namespace Appart\Modules\Notifications\Application\Event;

enum NotificationEventType: string
{
    case PreferenceObserved = 'notifications.preference.observed.v1';
    case TemplateObserved = 'notifications.template.observed.v1';
    case ChannelObserved = 'notifications.channel.observed.v1';
}
