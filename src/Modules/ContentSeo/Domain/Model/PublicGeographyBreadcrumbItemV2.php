<?php

namespace Appart\Modules\ContentSeo\Domain\Model;

use Appart\Modules\ContentSeo\Domain\Exception\InvalidSeoValue;

final readonly class PublicGeographyBreadcrumbItemV2
{
    public function __construct(public string $placeId, public string $type, public string $label)
    {
        if (trim($placeId) === '' || trim($type) === '' || trim($label) === '' || trim($label) !== $label) {
            throw InvalidSeoValue::field('public_geography_breadcrumb_v2');
        }
    }
}
