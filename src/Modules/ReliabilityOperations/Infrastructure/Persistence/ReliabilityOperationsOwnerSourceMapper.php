<?php

namespace Appart\Modules\ReliabilityOperations\Infrastructure\Persistence;

use Appart\Modules\ReliabilityOperations\Application\OwnerSource\ReliabilityOperationsRevisionState;
use Appart\Modules\ReliabilityOperations\Application\OwnerSource\ReliabilityOperationsStream;
use DateTimeImmutable;
use DateTimeZone;
use RuntimeException;
use Throwable;

/** @phpstan-type OwnerRow array{scope_key:string,stream_type:string,revision:int,decision:string,effective_at:string,recorded_at:string,revision_checksum:string} */
final readonly class ReliabilityOperationsOwnerSourceMapper
{
    /** @return OwnerRow */
    public function toRow(ReliabilityOperationsRevisionState $state): array
    {
        $row = [
            'scope_key' => $state->scopeKey,
            'stream_type' => $state->stream->value,
            'revision' => $state->revision,
            'decision' => $state->decision,
            'effective_at' => $this->canonical($state->effectiveAt),
            'recorded_at' => $this->canonical($state->recordedAt),
        ];

        return $row + ['revision_checksum' => $this->checksum($row)];
    }

    /** @param OwnerRow $row */
    public function toState(array $row): ReliabilityOperationsRevisionState
    {
        try {
            if (! hash_equals($row['revision_checksum'], $this->checksum($row))) {
                throw new RuntimeException('Reliability Operations owner source checksum mismatch.');
            }

            return new ReliabilityOperationsRevisionState($row['scope_key'], ReliabilityOperationsStream::from($row['stream_type']), $row['revision'], $row['decision'], new DateTimeImmutable($row['effective_at']), new DateTimeImmutable($row['recorded_at']));
        } catch (Throwable $exception) {
            throw new RuntimeException('Invalid Reliability Operations owner source row.', 0, $exception);
        }
    }

    /** @param array{scope_key:string,stream_type:string,revision:int,decision:string,effective_at:string,recorded_at:string} $row */
    private function checksum(array $row): string
    {
        return hash('sha256', implode("\n", [$row['scope_key'], $row['stream_type'], (string) $row['revision'], $row['decision'], $this->canonical(new DateTimeImmutable($row['effective_at'])), $this->canonical(new DateTimeImmutable($row['recorded_at']))]));
    }

    private function canonical(DateTimeImmutable $instant): string
    {
        return $instant->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d\TH:i:s.u\Z');
    }
}
