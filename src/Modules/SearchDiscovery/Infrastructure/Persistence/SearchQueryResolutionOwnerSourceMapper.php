<?php

namespace Appart\Modules\SearchDiscovery\Infrastructure\Persistence;

use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSource\SearchQueryResolutionRevisionDecision;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSource\SearchQueryResolutionRevisionState;
use DateTimeImmutable;
use DateTimeZone;
use RuntimeException;
use Throwable;

final readonly class SearchQueryResolutionOwnerSourceMapper
{
    /** @return array{query_fingerprint:string,revision:int,decision:string,effective_at:string,recorded_at:string,revision_checksum:string} */
    public function toRow(SearchQueryResolutionRevisionState $revision): array
    {
        $row = [
            'query_fingerprint' => $revision->queryFingerprint,
            'revision' => $revision->revision,
            'decision' => $revision->decision->value,
            'effective_at' => $this->canonical($revision->effectiveAt),
            'recorded_at' => $this->canonical($revision->recordedAt),
        ];

        return $row + ['revision_checksum' => $this->checksum($row)];
    }

    /** @param array<string, mixed> $row */
    public function toState(array $row): SearchQueryResolutionRevisionState
    {
        try {
            if (! hash_equals((string) $row['revision_checksum'], $this->checksum($row))) {
                throw new RuntimeException('Search query resolution checksum mismatch.');
            }

            return new SearchQueryResolutionRevisionState(
                (string) $row['query_fingerprint'],
                (int) $row['revision'],
                SearchQueryResolutionRevisionDecision::from((string) $row['decision']),
                new DateTimeImmutable((string) $row['effective_at']),
                new DateTimeImmutable((string) $row['recorded_at']),
            );
        } catch (Throwable $error) {
            throw new RuntimeException('Invalid Search query resolution row.', 0, $error);
        }
    }

    /** @param array<string, mixed> $row */
    public function checksum(array $row): string
    {
        return hash('sha256', implode("\n", [
            strtolower((string) $row['query_fingerprint']),
            (string) $row['revision'],
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
