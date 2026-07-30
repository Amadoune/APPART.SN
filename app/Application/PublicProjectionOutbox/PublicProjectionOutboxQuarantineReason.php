<?php

namespace App\Application\PublicProjectionOutbox;

enum PublicProjectionOutboxQuarantineReason: string
{
    case PermanentFailure = 'permanent_failure';
    case DivergentPayload = 'divergent_payload';
    case UnsupportedPayloadVersion = 'unsupported_payload_version';
    case UnsupportedEventType = 'unsupported_event_type';
    case AttemptsExhausted = 'attempts_exhausted';
    case DurableBlockage = 'durable_blockage';
}
