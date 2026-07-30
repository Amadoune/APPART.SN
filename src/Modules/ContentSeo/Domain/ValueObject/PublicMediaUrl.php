<?php

namespace Appart\Modules\ContentSeo\Domain\ValueObject;

use Appart\Modules\ContentSeo\Domain\Exception\InvalidSeoValue;

final readonly class PublicMediaUrl
{
    private function __construct(public string $value) {}

    public static function fromString(string $value): self
    {
        $value = trim($value);
        $parts = parse_url($value);
        if (
            $parts === false
            || ($parts['scheme'] ?? null) !== 'https'
            || ! isset($parts['host'], $parts['path'])
            || $parts['path'] === ''
            || isset($parts['user'], $parts['pass'], $parts['fragment'])
        ) {
            throw InvalidSeoValue::field('public_media_url');
        }

        return new self($value);
    }
}
