<?php

namespace Appart\Modules\Notifications\Application\Outbox;

enum NotificationOutboxStatus: string
{
    case Applied = 'applied';
    case AlreadyApplied = 'already_applied';
    case DivergentMessage = 'divergent_message';
    case DependencyUnavailable = 'dependency_unavailable';
}
