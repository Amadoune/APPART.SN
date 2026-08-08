<?php

namespace Appart\Modules\LegacyMigration\Infrastructure\Persistence;

use Appart\Modules\LegacyMigration\Application\OwnerSource\LegacyMigrationCutoverRevisionState;
use Appart\Modules\LegacyMigration\Application\OwnerSource\LegacyMigrationInventoryRevisionState;
use Appart\Modules\LegacyMigration\Application\OwnerSource\LegacyMigrationQuarantineRevisionState;
use Appart\Modules\LegacyMigration\Application\OwnerSource\LegacyMigrationReconciliationRevisionState;
use Appart\Modules\LegacyMigration\Application\OwnerSource\LegacyMigrationWaveRevisionState;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationCutoverStatusV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationInventoryStatusV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationQuarantineStatusV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationReconciliationStatusV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationWaveStatusV1;
use DateTimeImmutable;
use DateTimeZone;
use RuntimeException;
use Throwable;

/** @phpstan-type OwnerRow array{subject_key:string,stream_type:string,revision:int,decision:string,effective_at:string,recorded_at:string,revision_checksum:string} */
final readonly class LegacyMigrationOwnerSourceMapper
{
    /** @return OwnerRow */
    public function inventoryToRow(LegacyMigrationInventoryRevisionState $state): array
    {
        return $this->row($state->subjectKey, 'inventory', $state->revision, $state->decision->value, $state->effectiveAt, $state->recordedAt);
    }

    /** @return OwnerRow */
    public function waveToRow(LegacyMigrationWaveRevisionState $state): array
    {
        return $this->row($state->subjectKey, 'wave', $state->revision, $state->decision->value, $state->effectiveAt, $state->recordedAt);
    }

    /** @return OwnerRow */
    public function reconciliationToRow(LegacyMigrationReconciliationRevisionState $state): array
    {
        return $this->row($state->subjectKey, 'reconciliation', $state->revision, $state->decision->value, $state->effectiveAt, $state->recordedAt);
    }

    /** @return OwnerRow */
    public function quarantineToRow(LegacyMigrationQuarantineRevisionState $state): array
    {
        return $this->row($state->subjectKey, 'quarantine', $state->revision, $state->decision->value, $state->effectiveAt, $state->recordedAt);
    }

    /** @return OwnerRow */
    public function cutoverToRow(LegacyMigrationCutoverRevisionState $state): array
    {
        return $this->row($state->subjectKey, 'cutover', $state->revision, $state->decision->value, $state->effectiveAt, $state->recordedAt);
    }

    /** @param OwnerRow $row */
    public function toInventoryState(array $row): LegacyMigrationInventoryRevisionState
    {
        $this->assertValid($row, 'inventory');

        return new LegacyMigrationInventoryRevisionState($row['subject_key'], $row['revision'], LegacyMigrationInventoryStatusV1::from($row['decision']), new DateTimeImmutable($row['effective_at']), new DateTimeImmutable($row['recorded_at']));
    }

    /** @param OwnerRow $row */
    public function toWaveState(array $row): LegacyMigrationWaveRevisionState
    {
        $this->assertValid($row, 'wave');

        return new LegacyMigrationWaveRevisionState($row['subject_key'], $row['revision'], LegacyMigrationWaveStatusV1::from($row['decision']), new DateTimeImmutable($row['effective_at']), new DateTimeImmutable($row['recorded_at']));
    }

    /** @param OwnerRow $row */
    public function toReconciliationState(array $row): LegacyMigrationReconciliationRevisionState
    {
        $this->assertValid($row, 'reconciliation');

        return new LegacyMigrationReconciliationRevisionState($row['subject_key'], $row['revision'], LegacyMigrationReconciliationStatusV1::from($row['decision']), new DateTimeImmutable($row['effective_at']), new DateTimeImmutable($row['recorded_at']));
    }

    /** @param OwnerRow $row */
    public function toQuarantineState(array $row): LegacyMigrationQuarantineRevisionState
    {
        $this->assertValid($row, 'quarantine');

        return new LegacyMigrationQuarantineRevisionState($row['subject_key'], $row['revision'], LegacyMigrationQuarantineStatusV1::from($row['decision']), new DateTimeImmutable($row['effective_at']), new DateTimeImmutable($row['recorded_at']));
    }

    /** @param OwnerRow $row */
    public function toCutoverState(array $row): LegacyMigrationCutoverRevisionState
    {
        $this->assertValid($row, 'cutover');

        return new LegacyMigrationCutoverRevisionState($row['subject_key'], $row['revision'], LegacyMigrationCutoverStatusV1::from($row['decision']), new DateTimeImmutable($row['effective_at']), new DateTimeImmutable($row['recorded_at']));
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
                throw new RuntimeException('LegacyMigration owner source checksum mismatch.');
            }
        } catch (Throwable $exception) {
            throw new RuntimeException('Invalid LegacyMigration owner source row.', 0, $exception);
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
