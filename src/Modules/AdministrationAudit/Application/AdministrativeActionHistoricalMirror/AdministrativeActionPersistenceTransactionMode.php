<?php

namespace Appart\Modules\AdministrationAudit\Application\AdministrativeActionHistoricalMirror;

enum AdministrativeActionPersistenceTransactionMode: string
{
    case Local = 'local';
    case External = 'external';
}
