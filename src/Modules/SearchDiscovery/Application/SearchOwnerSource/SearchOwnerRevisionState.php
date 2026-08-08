<?php

namespace Appart\Modules\SearchDiscovery\Application\SearchOwnerSource;

use Appart\Modules\SearchDiscovery\Application\Decision\SearchDecision;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\SearchDocumentId;
use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;

final readonly class SearchOwnerRevisionState
{
    public DateTimeImmutable $effectiveAt;

    public DateTimeImmutable $recordedAt;

    public function __construct(
        public SearchDocumentId $documentId,
        public int $revision,
        public SearchOwnerRevisionDecision $decision,
        public SearchDecision $searchDecision,
        DateTimeImmutable $effectiveAt,
        DateTimeImmutable $recordedAt,
    ) {
        if ($revision < 1 || $searchDecision->version !== $revision || $searchDecision->projection->state->value !== $decision->value) {
            throw new InvalidArgumentException('Search owner revision is inconsistent.');
        }

        $utc = new DateTimeZone('UTC');
        $this->effectiveAt = $effectiveAt->setTimezone($utc);
        $this->recordedAt = $recordedAt->setTimezone($utc);
        if ($this->recordedAt < $this->effectiveAt) {
            throw new InvalidArgumentException('Search owner revision chronology is invalid.');
        }
    }
}
