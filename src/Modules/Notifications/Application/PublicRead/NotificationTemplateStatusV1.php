<?php

namespace Appart\Modules\Notifications\Application\PublicRead;

enum NotificationTemplateStatusV1: string
{
    case Available = 'available';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
