<?php

namespace Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle;

enum PropertyLifecyclePersistenceReadStatus: string
{
    case Found = 'found';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
}
