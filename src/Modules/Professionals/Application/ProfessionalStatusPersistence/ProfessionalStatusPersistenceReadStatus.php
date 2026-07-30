<?php

namespace Appart\Modules\Professionals\Application\ProfessionalStatusPersistence;

enum ProfessionalStatusPersistenceReadStatus: string
{
    case Found = 'found';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
}
