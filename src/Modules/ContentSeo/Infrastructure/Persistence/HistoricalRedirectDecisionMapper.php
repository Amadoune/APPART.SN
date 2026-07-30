<?php

namespace Appart\Modules\ContentSeo\Infrastructure\Persistence;

use Appart\Modules\ContentSeo\Application\HistoricalRedirect\HistoricalCanonical;
use Appart\Modules\ContentSeo\Application\HistoricalRedirect\HistoricalRedirectResolution;
use Appart\Modules\ContentSeo\Application\HistoricalRedirect\HistoricalRedirectTarget;
use Appart\Modules\ContentSeo\Domain\ValueObject\CanonicalUrl;
use Throwable;

final readonly class HistoricalRedirectDecisionMapper
{
    /** @param list<array<string, mixed>> $rows */
    public function toResolution(HistoricalCanonical $source, array $rows): HistoricalRedirectResolution
    {
        if ($rows === []) {
            return HistoricalRedirectResolution::notFound($source);
        }

        try {
            foreach ($rows as $row) {
                $this->assertIntact($source, $row);
            }
        } catch (Throwable) {
            return HistoricalRedirectResolution::corrupted($source);
        }

        if (count($rows) > 1) {
            return HistoricalRedirectResolution::ambiguous($source);
        }

        $destination = $rows[0]['destination_canonical'];
        if ($destination === null) {
            return HistoricalRedirectResolution::destinationMissing($source);
        }

        $target = HistoricalRedirectTarget::publicCanonical(CanonicalUrl::fromString((string) $destination));
        if ($target->canonical->value === $source->canonical->value) {
            return HistoricalRedirectResolution::loopDetected($source);
        }
        if ($rows[0]['destination_qualification'] === 'historical') {
            return HistoricalRedirectResolution::chainDetected($source);
        }

        return HistoricalRedirectResolution::resolved($source, $target);
    }

    /** @param array<string, mixed> $row */
    public function checksum(array $row): string
    {
        return hash('sha256', implode("\n", [
            (string) $row['decision_id'],
            (string) $row['historical_canonical'],
            $row['destination_canonical'] === null ? '' : (string) $row['destination_canonical'],
            $row['destination_qualification'] === null ? '' : (string) $row['destination_qualification'],
            (string) $row['revision'],
        ]));
    }

    /** @param array<string, mixed> $row */
    private function assertIntact(HistoricalCanonical $source, array $row): void
    {
        if (($row['integrity_status'] ?? null) !== 'intact'
            || ! is_string($row['decision_id'] ?? null)
            || preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/D', $row['decision_id']) !== 1
            || ! is_int($row['revision'] ?? null)
            || $row['revision'] < 1
            || ! is_string($row['historical_canonical'] ?? null)
            || CanonicalUrl::fromString($row['historical_canonical'])->value !== $row['historical_canonical']
            || $row['historical_canonical'] !== $source->canonical->value
            || ! is_string($row['decision_checksum'] ?? null)
            || ! hash_equals($row['decision_checksum'], $this->checksum($row))) {
            throw new \UnexpectedValueException('Corrupted historical redirect decision.');
        }

        $destination = $row['destination_canonical'] ?? null;
        $qualification = $row['destination_qualification'] ?? null;
        if ($destination === null && $qualification === null) {
            return;
        }
        if (! is_string($destination)
            || CanonicalUrl::fromString($destination)->value !== $destination
            || ! in_array($qualification, ['current', 'historical'], true)) {
            throw new \UnexpectedValueException('Corrupted historical redirect destination.');
        }
    }
}
