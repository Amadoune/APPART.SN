<?php

namespace Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSource;

use Appart\Modules\SearchDiscovery\Application\SearchExperiencePublicRead\SearchQuery;
use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;

final readonly class SearchQueryResolutionRevisionState
{
    public string $queryFingerprint;

    public DateTimeImmutable $effectiveAt;

    public DateTimeImmutable $recordedAt;

    public function __construct(
        SearchQuery|string $query,
        public int $revision,
        public SearchQueryResolutionRevisionDecision $decision,
        DateTimeImmutable $effectiveAt,
        DateTimeImmutable $recordedAt,
    ) {
        if ($revision < 1) {
            throw new InvalidArgumentException('Search query resolution revision must be positive.');
        }
        $this->queryFingerprint = is_string($query) ? self::validateFingerprint($query) : self::fingerprint($query);
        $utc = new DateTimeZone('UTC');
        $this->effectiveAt = $effectiveAt->setTimezone($utc);
        $this->recordedAt = $recordedAt->setTimezone($utc);
        if ($this->recordedAt < $this->effectiveAt) {
            throw new InvalidArgumentException('Search query resolution chronology is invalid.');
        }
    }

    public static function fingerprint(SearchQuery $query): string
    {
        return hash('sha256', $query->canonical());
    }

    private static function validateFingerprint(string $fingerprint): string
    {
        if (preg_match('/^[0-9a-f]{64}$/', $fingerprint) !== 1) {
            throw new InvalidArgumentException('Search query fingerprint is invalid.');
        }

        return $fingerprint;
    }
}
