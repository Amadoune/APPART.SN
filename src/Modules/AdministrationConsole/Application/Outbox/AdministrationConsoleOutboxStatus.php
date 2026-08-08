<?php

namespace Appart\Modules\AdministrationConsole\Application\Outbox;

enum AdministrationConsoleOutboxStatus: string
{
    case Applied = 'applied';
    case AlreadyApplied = 'already_applied';
    case DivergentMessage = 'divergent_message';
    case DependencyUnavailable = 'dependency_unavailable';
}
