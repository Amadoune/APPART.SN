<?php

namespace Appart\Modules\AdministrationAudit\Application\AdministrativeActionPersistenceCoexistence;

enum AdministrativeActionPersistenceWriteMode: string
{
    case ReadOnly = 'read_only';
    case HistoricalOnly = 'historical_only';
    case LifecycleOnly = 'lifecycle_only';
    case AtomicHistoricalAndLifecycle = 'atomic_historical_and_lifecycle';
}
