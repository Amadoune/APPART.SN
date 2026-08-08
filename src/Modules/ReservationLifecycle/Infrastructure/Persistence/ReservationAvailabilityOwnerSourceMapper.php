<?php

namespace Appart\Modules\ReservationLifecycle\Infrastructure\Persistence;

use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSource\ReservationAvailabilityRevisionDecision;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSource\ReservationAvailabilityRevisionState;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityPublicRead\ReservationAvailabilityIntentId;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityPublicRead\ReservationAvailabilitySubjectId;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityPublicRead\ReservationAvailabilityWindow;
use DateTimeImmutable;
use DateTimeZone;
use RuntimeException;
use Throwable;

final readonly class ReservationAvailabilityOwnerSourceMapper
{
    /** @return array{intent_id:string,revision:int,subject_id:string,window_start:string,window_end:string,decision:string,effective_at:string,recorded_at:string,checksum:string} */
    public function toRow(ReservationAvailabilityRevisionState $revision): array
    {
        $row = [
            'intent_id' => $revision->intentId->value,
            'revision' => $revision->revision,
            'subject_id' => $revision->subjectId->value,
            'window_start' => $this->canonical($revision->window->startsAt),
            'window_end' => $this->canonical($revision->window->endsAt),
            'decision' => $revision->decision->value,
            'effective_at' => $this->canonical($revision->effectiveAt),
            'recorded_at' => $this->canonical($revision->recordedAt),
        ];

        return $row + ['checksum' => $this->checksum($row)];
    }

    /** @param array<string, mixed> $row */
    public function toState(array $row): ReservationAvailabilityRevisionState
    {
        try {
            if (! hash_equals((string) $row['revision_checksum'], $this->checksum($row))) {
                throw new RuntimeException('Reservation availability revision checksum mismatch.');
            }

            return new ReservationAvailabilityRevisionState(
                ReservationAvailabilityIntentId::fromString((string) $row['availability_intent_id']),
                (int) $row['revision'],
                ReservationAvailabilitySubjectId::fromString((string) $row['availability_subject_id']),
                new ReservationAvailabilityWindow(
                    new DateTimeImmutable((string) $row['window_start']),
                    new DateTimeImmutable((string) $row['window_end']),
                ),
                ReservationAvailabilityRevisionDecision::from((string) $row['decision']),
                new DateTimeImmutable((string) $row['effective_at']),
                new DateTimeImmutable((string) $row['recorded_at']),
            );
        } catch (Throwable $error) {
            throw new RuntimeException('Invalid reservation availability owner source row.', 0, $error);
        }
    }

    /** @param array<string, mixed> $row */
    public function checksum(array $row): string
    {
        return hash('sha256', implode("\n", [
            strtolower((string) ($row['availability_intent_id'] ?? $row['intent_id'])),
            (string) $row['revision'],
            strtolower((string) ($row['availability_subject_id'] ?? $row['subject_id'])),
            $this->canonical(new DateTimeImmutable((string) $row['window_start'])),
            $this->canonical(new DateTimeImmutable((string) $row['window_end'])),
            (string) $row['decision'],
            $this->canonical(new DateTimeImmutable((string) $row['effective_at'])),
            $this->canonical(new DateTimeImmutable((string) $row['recorded_at'])),
        ]));
    }

    private function canonical(DateTimeImmutable $instant): string
    {
        return $instant->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d\TH:i:s.u\Z');
    }
}
