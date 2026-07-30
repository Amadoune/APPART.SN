<?php

namespace App\Application\ModerationOperationalAudit;

use App\Application\ModerationEventOutbox\Contract\ModerationOutboxReaderV1;
use App\Application\ModerationEventOutbox\ModerationOutboxDelivery;
use App\Application\ModerationEventRouting\ModerationRoutingDestination;
use App\Application\ModerationOperationalAudit\Contract\ModerationOperationalAuditDeliveryReaderV1;
use Appart\Modules\AdministrationAudit\Application\PublicAuditAppend\AdministrationAuditAppendResultV1;
use Appart\Modules\AdministrationAudit\Application\PublicAuditAppend\Contract\AdministrationAuditAppendV1;
use DateTimeImmutable;
use Throwable;

final readonly class ModerationOperationalAuditConsumer
{
    private const int MAX_ATTEMPTS = 3;

    public function __construct(
        private ModerationOperationalAuditDeliveryReaderV1|ModerationOutboxReaderV1 $outbox,
        private AdministrationAuditAppendV1 $audit,
        private ModerationOperationalAuditRecordFactory $records,
    ) {}

    public function consumeNext(string $owner, DateTimeImmutable $now): ?ModerationOperationalAuditStatus
    {
        try {
            $delivery = $this->claim($owner, $now);
            if ($delivery === null) {
                return null;
            }
            $record = $this->records->map(
                $delivery instanceof ModerationOperationalAuditDelivery
                    ? $delivery->message
                    : $delivery->message->event,
            );
            if ($record === null) {
                return $this->quarantine($delivery, 'corrupted_audit_source');
            }

            return match ($this->audit->append($record)) {
                AdministrationAuditAppendResultV1::Applied => $this->delivered(
                    $delivery,
                    $now,
                    ModerationOperationalAuditStatus::Applied,
                ),
                AdministrationAuditAppendResultV1::AlreadyApplied => $this->delivered(
                    $delivery,
                    $now,
                    ModerationOperationalAuditStatus::AlreadyApplied,
                ),
                AdministrationAuditAppendResultV1::DivergentRecord => $this->quarantine(
                    $delivery,
                    'divergent_audit_record',
                ),
                AdministrationAuditAppendResultV1::Rejected => $this->quarantine(
                    $delivery,
                    'rejected_audit_record',
                ),
                AdministrationAuditAppendResultV1::DependencyUnavailable => $this->retry(
                    $delivery,
                    $now,
                ),
            };
        } catch (CorruptedOperationalAuditDelivery) {
            return ModerationOperationalAuditStatus::Quarantined;
        } catch (Throwable) {
            return ModerationOperationalAuditStatus::DependencyUnavailable;
        }
    }

    private function delivered(
        ModerationOperationalAuditDelivery|ModerationOutboxDelivery $delivery,
        DateTimeImmutable $now,
        ModerationOperationalAuditStatus $status,
    ): ModerationOperationalAuditStatus {
        return $this->outbox->markDelivered($delivery, $now)
            ? $status
            : ModerationOperationalAuditStatus::DependencyUnavailable;
    }

    private function retry(
        ModerationOperationalAuditDelivery|ModerationOutboxDelivery $delivery,
        DateTimeImmutable $now,
    ): ModerationOperationalAuditStatus {
        if ($delivery->attempt >= self::MAX_ATTEMPTS) {
            return $this->quarantine($delivery, 'audit_dependency_unavailable');
        }
        $scheduled = $this->outbox->retry(
            $delivery,
            $now->modify('+'.$delivery->attempt.' seconds'),
            'audit_dependency_unavailable',
        );

        return $scheduled
            ? ModerationOperationalAuditStatus::RetryScheduled
            : ModerationOperationalAuditStatus::DependencyUnavailable;
    }

    private function quarantine(
        ModerationOperationalAuditDelivery|ModerationOutboxDelivery $delivery,
        string $code,
    ): ModerationOperationalAuditStatus {
        return $this->outbox->quarantine($delivery, $code)
            ? ModerationOperationalAuditStatus::Quarantined
            : ModerationOperationalAuditStatus::DependencyUnavailable;
    }

    private function claim(
        string $owner,
        DateTimeImmutable $now,
    ): ModerationOperationalAuditDelivery|ModerationOutboxDelivery|null {
        return $this->outbox instanceof ModerationOperationalAuditDeliveryReaderV1
            ? $this->outbox->claimNext($owner, $now)
            : $this->outbox->claimNextForDestination(
                $owner,
                ModerationRoutingDestination::DeliveryObservation,
                $now,
            );
    }
}
