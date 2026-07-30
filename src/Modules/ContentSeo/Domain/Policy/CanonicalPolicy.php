<?php

namespace Appart\Modules\ContentSeo\Domain\Policy;

use Appart\Modules\ContentSeo\Domain\ValueObject\CanonicalUrl;

final readonly class CanonicalPolicy
{
    public function fromPath(string $path): CanonicalUrl
    {
        return CanonicalUrl::fromString('https://appart.sn/'.ltrim($path, '/'));
    }
}
