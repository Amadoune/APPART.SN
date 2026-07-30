<?php

namespace Appart\Modules\ContentSeo\Domain\Model;

use Appart\Modules\ContentSeo\Domain\ValueObject\CanonicalDisposition;
use Appart\Modules\ContentSeo\Domain\ValueObject\CanonicalUrl;
use DateTimeImmutable;

final readonly class CanonicalHistoryEntry
{
    public function __construct(
        public CanonicalUrl $canonical,
        public CanonicalDisposition $disposition,
        public DateTimeImmutable $effectiveAt,
        public ?DateTimeImmutable $replacedAt = null,
        public ?CanonicalUrl $redirectTarget = null,
    ) {}
}
