<?php

namespace Appart\Modules\ContentSeo\Domain\ValueObject;

use Appart\Modules\ContentSeo\Domain\Exception\InvalidSeoValue;

final readonly class SitemapPriority
{
    private function __construct(public int $percent) {}

    public static function fromPercent(int $percent): self
    {
        if ($percent < 0 || $percent > 100) {
            throw InvalidSeoValue::field('sitemap_priority');
        }

        return new self($percent);
    }
}
