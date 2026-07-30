<?php

namespace Appart\Modules\ContentSeo\Domain\ValueObject;

use Appart\Modules\ContentSeo\Domain\Exception\InvalidSeoValue;

final readonly class SeoTitle
{
    private function __construct(public string $value) {}

    public static function fromString(string $value): self
    {
        $value = trim((string) preg_replace('/\s+/u', ' ', $value));
        if (mb_strlen($value) < 10 || mb_strlen($value) > 70) {
            throw InvalidSeoValue::field('seo_title');
        }

        return new self($value);
    }
}
