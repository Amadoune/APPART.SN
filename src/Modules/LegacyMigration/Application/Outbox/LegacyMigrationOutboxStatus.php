<?php

namespace Appart\Modules\LegacyMigration\Application\Outbox;

enum LegacyMigrationOutboxStatus: string
{
    case Applied = 'applied';
    case AlreadyApplied = 'already_applied';
    case DivergentMessage = 'divergent_message';
    case DependencyUnavailable = 'dependency_unavailable';
}
