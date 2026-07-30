<?php

namespace Appart\Modules\ContentSeo\Infrastructure\Persistence;

use Appart\Modules\ContentSeo\Application\HistoricalCanonicalQualification\HistoricalCanonicalQualification;
use Appart\Modules\ContentSeo\Domain\ValueObject\CanonicalUrl;
use Throwable;
use UnexpectedValueException;

final readonly class HistoricalCanonicalQualificationMapper
{
    /** @param list<array<string, mixed>> $rows */
    public function toQualification(CanonicalUrl $canonical, array $rows): HistoricalCanonicalQualification
    {
        if ($rows === []) {
            return HistoricalCanonicalQualification::unknown($canonical);
        }

        try {
            foreach ($rows as $row) {
                $this->assertIntact($canonical, $row);
            }
        } catch (Throwable) {
            return HistoricalCanonicalQualification::corrupted($canonical);
        }

        if (count($rows) > 1) {
            return HistoricalCanonicalQualification::ambiguous($canonical);
        }

        if ($rows[0]['qualification'] === 'current') {
            return HistoricalCanonicalQualification::current($canonical);
        }

        return HistoricalCanonicalQualification::historical($canonical);
    }

    /** @param array<string, mixed> $row */
    public function checksum(array $row): string
    {
        return hash('sha256', implode("\n", [
            (string) $row['decision_id'],
            (string) $row['canonical'],
            (string) $row['qualification'],
            (string) $row['revision'],
        ]));
    }

    /** @param array<string, mixed> $row */
    private function assertIntact(CanonicalUrl $canonical, array $row): void
    {
        if (($row['integrity_status'] ?? null) !== 'intact'
            || ! is_string($row['decision_id'] ?? null)
            || preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/D', $row['decision_id']) !== 1
            || ! is_string($row['canonical'] ?? null)
            || CanonicalUrl::fromString($row['canonical'])->value !== $row['canonical']
            || $row['canonical'] !== $canonical->value
            || ! in_array($row['qualification'] ?? null, ['current', 'historical'], true)
            || ! is_int($row['revision'] ?? null)
            || $row['revision'] < 1
            || ! is_string($row['decision_checksum'] ?? null)
            || ! hash_equals($row['decision_checksum'], $this->checksum($row))) {
            throw new UnexpectedValueException('Corrupted historical canonical qualification.');
        }
    }
}
