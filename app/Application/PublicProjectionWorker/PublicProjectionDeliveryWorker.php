<?php

namespace App\Application\PublicProjectionWorker;

use App\Application\PublicProjectionDelivery\Contract\PublicProjectionDeliveryConsumer;
use App\Application\PublicProjectionDelivery\Contract\PublicProjectionRoutedDeliveryConsumerV1;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryConsumptionResult;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryMessage;
use App\Application\PublicProjectionOutbox\Contract\PublicProjectionOutboxClaimManager;
use App\Application\PublicProjectionOutbox\Contract\PublicProjectionOutboxReader;
use App\Application\PublicProjectionOutbox\Contract\PublicProjectionOutboxRetryPolicy;
use App\Application\PublicProjectionOutbox\Contract\PublicProjectionOutboxWriter;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxClaimOwnerId;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxClaimResult;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxConsumerId;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxLease;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxQuarantineDecision;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxQuarantineReason;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxRecord;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxRetryClassification;
use App\Application\PublicProjectionWorker\Contract\PublicProjectionDeliveryClock;
use DateInterval;
use Throwable;

final readonly class PublicProjectionDeliveryWorker
{
    public function __construct(
        private PublicProjectionOutboxReader $reader,
        private PublicProjectionOutboxClaimManager $claims,
        private PublicProjectionOutboxWriter $writer,
        private PublicProjectionDeliveryConsumerRegistry $consumers,
        private PublicProjectionOutboxRetryPolicy $retryPolicy,
        private PublicProjectionDeliveryClock $clock,
        private PublicProjectionDeliveryWorkerId $workerId,
        private int $batchSize,
        private int $leaseSeconds,
    ) {
        if ($batchSize < 1 || $leaseSeconds < 1) {
            throw new \InvalidArgumentException('Batch size and lease duration must be positive.');
        }
    }

    public function runOnce(PublicProjectionOutboxConsumerId $consumerId): PublicProjectionDeliveryBatchResult
    {
        $records = $this->reader->findClaimable($consumerId, $this->batchSize);
        $outcomes = [];
        $claimed = 0;
        $activeAggregates = [];
        $observedAt = $this->clock->now();
        $oldestMessageLagSeconds = 0;

        foreach ($records as $record) {
            $oldestMessageLagSeconds = max($oldestMessageLagSeconds, $observedAt->getTimestamp() - $record->message->recordedAt->getTimestamp());
            $aggregate = $this->aggregateKey($record->message);
            if (isset($activeAggregates[$aggregate])) {
                $outcomes[] = new PublicProjectionDeliveryMessageOutcome($record->message->messageId, PublicProjectionDeliveryOutcome::DeferredByCausality);

                continue;
            }
            $activeAggregates[$aggregate] = true;
            $now = $this->clock->now();
            $owner = PublicProjectionOutboxClaimOwnerId::fromString($this->workerId->value);
            $lease = new PublicProjectionOutboxLease($owner, $now, $now->add(new DateInterval("PT{$this->leaseSeconds}S")));
            $claim = $this->claims->claim($record->message, $consumerId, $lease);
            if ($claim !== PublicProjectionOutboxClaimResult::Claimed && $claim !== PublicProjectionOutboxClaimResult::LeaseExpired) {
                $outcomes[] = new PublicProjectionDeliveryMessageOutcome($record->message->messageId, PublicProjectionDeliveryOutcome::ClaimConflict, $claim->value);

                continue;
            }
            $claimed++;
            $outcomes[] = $this->deliver($record, $consumerId, $owner, $claim === PublicProjectionOutboxClaimResult::LeaseExpired);
        }

        return new PublicProjectionDeliveryBatchResult(count($records), $claimed, $outcomes, $oldestMessageLagSeconds);
    }

    private function deliver(PublicProjectionOutboxRecord $record, PublicProjectionOutboxConsumerId $consumerId, PublicProjectionOutboxClaimOwnerId $owner, bool $expired): PublicProjectionDeliveryMessageOutcome
    {
        $resolution = $this->consumers->resolve($consumerId, $record->message);
        if ($resolution->failure !== null) {
            return $this->apply($record, $consumerId, $owner, $resolution->failure, $expired);
        }

        try {
            $result = match ($resolution->mode) {
                PublicProjectionDeliveryMode::Legacy => $resolution->consumer instanceof PublicProjectionDeliveryConsumer
                    ? $resolution->consumer->consume($record->message)
                    : PublicProjectionDeliveryConsumptionResult::PermanentFailure,
                PublicProjectionDeliveryMode::RoutedV1 => $resolution->consumer instanceof PublicProjectionRoutedDeliveryConsumerV1
                    && $record->routedDelivery !== null
                    ? $resolution->consumer->consumeRouted($record->routedDelivery)
                    : PublicProjectionDeliveryConsumptionResult::DivergentPayload,
                null => PublicProjectionDeliveryConsumptionResult::PermanentFailure,
            };
        } catch (Throwable) {
            $result = PublicProjectionDeliveryConsumptionResult::RetryableFailure;
        }

        return $this->apply($record, $consumerId, $owner, $result, $expired);
    }

    private function apply(PublicProjectionOutboxRecord $record, PublicProjectionOutboxConsumerId $consumerId, PublicProjectionOutboxClaimOwnerId $owner, PublicProjectionDeliveryConsumptionResult $result, bool $expired): PublicProjectionDeliveryMessageOutcome
    {
        $message = $record->message;
        $outcome = match ($result) {
            PublicProjectionDeliveryConsumptionResult::Consumed => $this->delivered($message, $consumerId, $owner, PublicProjectionDeliveryOutcome::Delivered),
            PublicProjectionDeliveryConsumptionResult::AlreadyConsumed => $this->delivered($message, $consumerId, $owner, PublicProjectionDeliveryOutcome::AlreadyConsumed),
            PublicProjectionDeliveryConsumptionResult::RejectedObsolete => $this->delivered($message, $consumerId, $owner, PublicProjectionDeliveryOutcome::Obsolete),
            PublicProjectionDeliveryConsumptionResult::BlockedBySequenceGap => $this->blocked($record, $consumerId, $owner, PublicProjectionOutboxRetryClassification::SequenceGap, PublicProjectionDeliveryOutcome::BlockedBySequenceGap),
            PublicProjectionDeliveryConsumptionResult::BlockedBySourceReadiness => $this->blocked($record, $consumerId, $owner, PublicProjectionOutboxRetryClassification::SourceNotReady, PublicProjectionDeliveryOutcome::BlockedBySourceReadiness),
            PublicProjectionDeliveryConsumptionResult::UnsupportedEventType => $this->quarantined($record, $consumerId, $owner, PublicProjectionOutboxQuarantineReason::UnsupportedEventType, $result->value, PublicProjectionDeliveryOutcome::Unsupported),
            PublicProjectionDeliveryConsumptionResult::UnsupportedPayloadVersion => $this->quarantined($record, $consumerId, $owner, PublicProjectionOutboxQuarantineReason::UnsupportedPayloadVersion, $result->value, PublicProjectionDeliveryOutcome::Unsupported),
            PublicProjectionDeliveryConsumptionResult::DivergentPayload => $this->quarantined($record, $consumerId, $owner, PublicProjectionOutboxQuarantineReason::DivergentPayload, $result->value, PublicProjectionDeliveryOutcome::Quarantined),
            PublicProjectionDeliveryConsumptionResult::RetryableFailure => $this->retry($record, $consumerId, $owner),
            PublicProjectionDeliveryConsumptionResult::PermanentFailure => $this->quarantined($record, $consumerId, $owner, PublicProjectionOutboxQuarantineReason::PermanentFailure, $result->value, PublicProjectionDeliveryOutcome::Quarantined),
        };

        return $expired ? new PublicProjectionDeliveryMessageOutcome($message->messageId, PublicProjectionDeliveryOutcome::LeaseExpired, $outcome->outcome->value) : $outcome;
    }

    private function delivered(PublicProjectionDeliveryMessage $message, PublicProjectionOutboxConsumerId $consumer, PublicProjectionOutboxClaimOwnerId $owner, PublicProjectionDeliveryOutcome $outcome): PublicProjectionDeliveryMessageOutcome
    {
        $write = $this->writer->markDelivered($message, $consumer, $owner);

        return new PublicProjectionDeliveryMessageOutcome($message->messageId, $outcome, $write->value);
    }

    private function blocked(PublicProjectionOutboxRecord $record, PublicProjectionOutboxConsumerId $consumer, PublicProjectionOutboxClaimOwnerId $owner, PublicProjectionOutboxRetryClassification $classification, PublicProjectionDeliveryOutcome $outcome): PublicProjectionDeliveryMessageOutcome
    {
        $decision = $this->retryPolicy->decide($classification, $record->attempts->incremented());
        $write = $this->writer->scheduleRetry($record->message, $consumer, $owner, $decision);

        return new PublicProjectionDeliveryMessageOutcome($record->message->messageId, $outcome, $write->value);
    }

    private function retry(PublicProjectionOutboxRecord $record, PublicProjectionOutboxConsumerId $consumer, PublicProjectionOutboxClaimOwnerId $owner): PublicProjectionDeliveryMessageOutcome
    {
        $decision = $this->retryPolicy->decide(PublicProjectionOutboxRetryClassification::Transient, $record->attempts->incremented());
        if (! $decision->retryAllowed) {
            return $this->quarantined($record, $consumer, $owner, PublicProjectionOutboxQuarantineReason::AttemptsExhausted, 'attempts_exhausted', PublicProjectionDeliveryOutcome::Quarantined);
        }
        $write = $this->writer->scheduleRetry($record->message, $consumer, $owner, $decision);

        return new PublicProjectionDeliveryMessageOutcome($record->message->messageId, PublicProjectionDeliveryOutcome::RetryScheduled, $write->value);
    }

    private function quarantined(PublicProjectionOutboxRecord $record, PublicProjectionOutboxConsumerId $consumer, PublicProjectionOutboxClaimOwnerId $owner, PublicProjectionOutboxQuarantineReason $reason, string $code, PublicProjectionDeliveryOutcome $outcome): PublicProjectionDeliveryMessageOutcome
    {
        $write = $this->writer->quarantine($record->message, $consumer, $owner, new PublicProjectionOutboxQuarantineDecision($reason, $code));

        return new PublicProjectionDeliveryMessageOutcome($record->message->messageId, $outcome, $write->value);
    }

    private function aggregateKey(PublicProjectionDeliveryMessage $message): string
    {
        return implode(':', [$message->sourceModule->value, $message->aggregateType->value, $message->aggregateId->value]);
    }
}
