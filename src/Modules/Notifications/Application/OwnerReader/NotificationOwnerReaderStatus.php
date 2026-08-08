<?php

namespace Appart\Modules\Notifications\Application\OwnerReader;

enum NotificationOwnerReaderStatus: string
{
    case Enabled = 'enabled';
    case Disabled = 'disabled';
    case Available = 'available';
    case Allowed = 'allowed';
    case Blocked = 'blocked';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
