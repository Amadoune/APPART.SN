<?php

namespace Appart\Modules\ContentSeo\Domain\ValueObject;

use Appart\Modules\ContentSeo\Domain\Exception\InvalidSeoValue;

final readonly class MetaDescription
{
    private function __construct(public string $value) {}

    public static function fromString(string $value): self
    {
        $value = trim((string) preg_replace('/\s+/u', ' ', $value));
        if (mb_strlen($value) < 50 || mb_strlen($value) > 170) {
            throw InvalidSeoValue::field('meta_description');
        }

        return new self($value);
    }
}
