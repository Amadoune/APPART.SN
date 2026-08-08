<?php

namespace Appart\Modules\Notifications\Application\PublicRead;

enum NotificationPreferenceStatusV1: string
{
    case Enabled = 'enabled';
    case Disabled = 'disabled';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
