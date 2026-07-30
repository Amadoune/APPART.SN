<?php

namespace Appart\Modules\ContentSeo\Application\HistoricalRedirect;

use Appart\Modules\ContentSeo\Domain\ValueObject\CanonicalUrl;

final readonly class HistoricalCanonical
{
    private function __construct(public CanonicalUrl $canonical) {}

    public static function declared(CanonicalUrl $canonical): self
    {
        return new self($canonical);
    }
}
