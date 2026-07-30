<?php

namespace App\Application\IdentityAccessEventDelivery;

final class IdentityAccessReplayRetryPolicy
{
    public function decide(IdentityAccessDeliveryOutcome $outcome, int $attempt): IdentityAccessRetryDecision
    {
        return match ($outcome) {
            IdentityAccessDeliveryOutcome::Consumed,
            IdentityAccessDeliveryOutcome::AlreadyConsumed => IdentityAccessRetryDecision::Complete,
            IdentityAccessDeliveryOutcome::TransientFailure => $attempt < 5
                ? IdentityAccessRetryDecision::Retry
                : IdentityAccessRetryDecision::Quarantine,
            IdentityAccessDeliveryOutcome::PermanentFailure,
            IdentityAccessDeliveryOutcome::DivergentReplay => IdentityAccessRetryDecision::Quarantine,
        };
    }
}
