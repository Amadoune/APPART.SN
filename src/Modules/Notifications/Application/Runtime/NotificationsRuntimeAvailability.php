<?php

namespace Appart\Modules\Notifications\Application\Runtime;

enum NotificationsRuntimeAvailability: string
{
    case Available = 'available';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
