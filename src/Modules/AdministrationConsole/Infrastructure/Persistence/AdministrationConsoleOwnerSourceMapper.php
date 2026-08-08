<?php

namespace Appart\Modules\AdministrationConsole\Infrastructure\Persistence;

use Appart\Modules\AdministrationConsole\Application\OwnerSource\AdministrationAuditRevisionState;
use Appart\Modules\AdministrationConsole\Application\OwnerSource\AdministrationOperatorRevisionState;
use Appart\Modules\AdministrationConsole\Application\OwnerSource\AdministrationQueueRevisionState;
use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationAuditStatusV1;
use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationOperatorStatusV1;
use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationQueueStatusV1;
use DateTimeImmutable;
use DateTimeZone;
use RuntimeException;
use Throwable;

/** @phpstan-type OwnerRow array{subject_key:string,stream_type:string,revision:int,decision:string,effective_at:string,recorded_at:string,revision_checksum:string} */
final readonly class AdministrationConsoleOwnerSourceMapper
{
    /** @return OwnerRow */
    public function operatorToRow(AdministrationOperatorRevisionState $state): array
    {
        return $this->row($state->subjectKey, 'operator', $state->revision, $state->decision->value, $state->effectiveAt, $state->recordedAt);
    }

    /** @return OwnerRow */
    public function queueToRow(AdministrationQueueRevisionState $state): array
    {
        return $this->row($state->subjectKey, 'queue', $state->revision, $state->decision->value, $state->effectiveAt, $state->recordedAt);
    }

    /** @return OwnerRow */
    public function auditToRow(AdministrationAuditRevisionState $state): array
    {
        return $this->row($state->subjectKey, 'audit', $state->revision, $state->decision->value, $state->effectiveAt, $state->recordedAt);
    }

    /** @param OwnerRow $row */
    public function toOperatorState(array $row): AdministrationOperatorRevisionState
    {
        $this->assertValid($row, 'operator');

        return new AdministrationOperatorRevisionState($row['subject_key'], $row['revision'], AdministrationOperatorStatusV1::from($row['decision']), new DateTimeImmutable($row['effective_at']), new DateTimeImmutable($row['recorded_at']));
    }

    /** @param OwnerRow $row */
    public function toQueueState(array $row): AdministrationQueueRevisionState
    {
        $this->assertValid($row, 'queue');

        return new AdministrationQueueRevisionState($row['subject_key'], $row['revision'], AdministrationQueueStatusV1::from($row['decision']), new DateTimeImmutable($row['effective_at']), new DateTimeImmutable($row['recorded_at']));
    }

    /** @param OwnerRow $row */
    public function toAuditState(array $row): AdministrationAuditRevisionState
    {
        $this->assertValid($row, 'audit');

        return new AdministrationAuditRevisionState($row['subject_key'], $row['revision'], AdministrationAuditStatusV1::from($row['decision']), new DateTimeImmutable($row['effective_at']), new DateTimeImmutable($row['recorded_at']));
    }

    /** @return OwnerRow */
    private function row(string $key, string $stream, int $revision, string $decision, DateTimeImmutable $effective, DateTimeImmutable $recorded): array
    {
        $row = ['subject_key' => $key, 'stream_type' => $stream, 'revision' => $revision, 'decision' => $decision, 'effective_at' => $this->canonical($effective), 'recorded_at' => $this->canonical($recorded)];

        return $row + ['revision_checksum' => $this->checksum($row)];
    }

    /** @param OwnerRow $row */
    private function assertValid(array $row, string $stream): void
    {
        try {
            if ($row['stream_type'] !== $stream || ! hash_equals($row['revision_checksum'], $this->checksum($row))) {
                throw new RuntimeException('AdministrationConsole owner source checksum mismatch.');
            }
        } catch (Throwable $exception) {
            throw new RuntimeException('Invalid AdministrationConsole owner source row.', 0, $exception);
        }
    }

    /** @param array{subject_key:string,stream_type:string,revision:int,decision:string,effective_at:string,recorded_at:string} $row */
    private function checksum(array $row): string
    {
        return hash('sha256', implode("\n", [$row['subject_key'], $row['stream_type'], (string) $row['revision'], $row['decision'], $this->canonical(new DateTimeImmutable($row['effective_at'])), $this->canonical(new DateTimeImmutable($row['recorded_at']))]));
    }

    private function canonical(DateTimeImmutable $instant): string
    {
        return $instant->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d\TH:i:s.u\Z');
    }
}
