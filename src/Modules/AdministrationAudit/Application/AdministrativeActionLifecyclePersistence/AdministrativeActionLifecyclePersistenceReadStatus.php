<?php

namespace Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecyclePersistence;

enum AdministrativeActionLifecyclePersistenceReadStatus: string
{
    case Found = 'found';
    case NotEnrolled = 'not_enrolled';
    case Corrupted = 'corrupted';
}
