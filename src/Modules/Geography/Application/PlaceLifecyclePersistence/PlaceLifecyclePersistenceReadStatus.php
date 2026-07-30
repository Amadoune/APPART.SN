<?php

namespace Appart\Modules\Geography\Application\PlaceLifecyclePersistence;

enum PlaceLifecyclePersistenceReadStatus: string
{
    case Found = 'found';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
}
