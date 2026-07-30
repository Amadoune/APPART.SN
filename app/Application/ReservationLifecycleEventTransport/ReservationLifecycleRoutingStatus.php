<?php

namespace App\Application\ReservationLifecycleEventTransport;

enum ReservationLifecycleRoutingStatus: string
{
    case Stored = 'stored';
    case AlreadyStored = 'already_stored';
    case CorruptedEnvelope = 'corrupted_envelope';
    case PersistenceCorrupted = 'persistence_corrupted';
}
