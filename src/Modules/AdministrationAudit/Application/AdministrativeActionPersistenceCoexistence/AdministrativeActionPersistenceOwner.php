<?php

namespace Appart\Modules\AdministrationAudit\Application\AdministrativeActionPersistenceCoexistence;

enum AdministrativeActionPersistenceOwner: string
{
    case HistoricalRegistry = 'historical_registry';
    case LifecycleJournal = 'lifecycle_journal';
}
