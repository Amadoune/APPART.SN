<?php

namespace App\Application\PublicProjectionRetry;

use App\Application\PublicProjectionOutbox\PublicProjectionOutboxQuarantineReason;

final readonly class PublicProjectionQuarantineReleasePolicy
{
    public function decide(PublicProjectionOutboxQuarantineReason $reason, ?PublicProjectionReplayAuthorization $authorization): PublicProjectionReplayDecision
    {
        if ($authorization === null) {
            return PublicProjectionReplayDecision::DeniedMissingAuthorization;
        }

        return match ($reason) {
            PublicProjectionOutboxQuarantineReason::UnsupportedEventType,
            PublicProjectionOutboxQuarantineReason::UnsupportedPayloadVersion,
            PublicProjectionOutboxQuarantineReason::DivergentPayload => PublicProjectionReplayDecision::DeniedTerminalIncompatibility,
            PublicProjectionOutboxQuarantineReason::PermanentFailure,
            PublicProjectionOutboxQuarantineReason::AttemptsExhausted,
            PublicProjectionOutboxQuarantineReason::DurableBlockage => PublicProjectionReplayDecision::Authorized,
        };
    }
}
