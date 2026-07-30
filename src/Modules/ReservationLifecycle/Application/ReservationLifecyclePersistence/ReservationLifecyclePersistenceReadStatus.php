<?php

namespace Appart\Modules\ReservationLifecycle\Application\ReservationLifecyclePersistence;

enum ReservationLifecyclePersistenceReadStatus: string
{
    case Found = 'found';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
}
