<?php

namespace App\Application\MediaIngestionEventDelivery;

final class MediaIngestionReplayRetryPolicy
{
    public function decide(MediaIngestionDeliveryOutcome $outcome, int $attempt): MediaIngestionRetryDecision
    {
        return match ($outcome) {
            MediaIngestionDeliveryOutcome::Consumed,
            MediaIngestionDeliveryOutcome::AlreadyConsumed => MediaIngestionRetryDecision::Complete,
            MediaIngestionDeliveryOutcome::TransientFailure => $attempt < 5
                ? MediaIngestionRetryDecision::Retry
                : MediaIngestionRetryDecision::Quarantine,
            MediaIngestionDeliveryOutcome::PermanentFailure,
            MediaIngestionDeliveryOutcome::DivergentReplay => MediaIngestionRetryDecision::Quarantine,
        };
    }
}
