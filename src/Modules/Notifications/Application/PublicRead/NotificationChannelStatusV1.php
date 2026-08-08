<?php

namespace Appart\Modules\Notifications\Application\PublicRead;

enum NotificationChannelStatusV1: string
{
    case Allowed = 'allowed';
    case Blocked = 'blocked';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
