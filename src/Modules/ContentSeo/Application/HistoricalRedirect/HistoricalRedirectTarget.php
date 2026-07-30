<?php

namespace Appart\Modules\ContentSeo\Application\HistoricalRedirect;

use Appart\Modules\ContentSeo\Domain\ValueObject\CanonicalUrl;

final readonly class HistoricalRedirectTarget
{
    private function __construct(public CanonicalUrl $canonical) {}

    public static function publicCanonical(CanonicalUrl $canonical): self
    {
        return new self($canonical);
    }
}
