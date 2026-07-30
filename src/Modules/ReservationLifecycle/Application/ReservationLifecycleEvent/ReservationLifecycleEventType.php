<?php

namespace Appart\Modules\ReservationLifecycle\Application\ReservationLifecycleEvent;

enum ReservationLifecycleEventType: string
{
    case ReservationSubmitted = 'reservation.lifecycle.submitted';
    case ReservationCancelledFromDraft = 'reservation.lifecycle.cancelled_from_draft';
    case ReservationConfirmed = 'reservation.lifecycle.confirmed';
    case ReservationRejected = 'reservation.lifecycle.rejected';
    case ReservationCancelledFromRequested = 'reservation.lifecycle.cancelled_from_requested';
    case ReservationExpiredFromRequested = 'reservation.lifecycle.expired_from_requested';
    case ReservationStarted = 'reservation.lifecycle.started';
    case ReservationCancelledFromConfirmed = 'reservation.lifecycle.cancelled_from_confirmed';
    case ReservationExpiredFromConfirmed = 'reservation.lifecycle.expired_from_confirmed';
    case ReservationCompleted = 'reservation.lifecycle.completed';
    case ReservationCancelledInProgress = 'reservation.lifecycle.cancelled_in_progress';
}
