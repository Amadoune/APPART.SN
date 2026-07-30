<?php

namespace App\Application\PublicProjectionDelivery;

enum PublicProjectionDeliveryConsumptionResult: string
{
    case Consumed = 'consumed';
    case AlreadyConsumed = 'already_consumed';
    case RejectedObsolete = 'rejected_obsolete';
    case BlockedBySequenceGap = 'blocked_by_sequence_gap';
    case BlockedBySourceReadiness = 'blocked_by_source_readiness';
    case UnsupportedEventType = 'unsupported_event_type';
    case UnsupportedPayloadVersion = 'unsupported_payload_version';
    case DivergentPayload = 'divergent_payload';
    case RetryableFailure = 'retryable_failure';
    case PermanentFailure = 'permanent_failure';
}
