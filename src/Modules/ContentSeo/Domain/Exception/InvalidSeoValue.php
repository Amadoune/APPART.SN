<?php

namespace Appart\Modules\ContentSeo\Domain\Exception;

final class InvalidSeoValue extends SeoException
{
    public static function field(string $field): self
    {
        return new self("Invalid SEO value: {$field}.");
    }
}
