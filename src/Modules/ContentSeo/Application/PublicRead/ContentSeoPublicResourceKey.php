<?php

namespace Appart\Modules\ContentSeo\Application\PublicRead;

use InvalidArgumentException;

final readonly class ContentSeoPublicResourceKey
{
    public function __construct(public string $value)
    {
        if ($value === '' || trim($value) !== $value || strlen($value) > 255) {
            throw new InvalidArgumentException('The public ContentSeo resource key must be canonical.');
        }
    }

    public function canonical(): string
    {
        return $this->value;
    }
}
