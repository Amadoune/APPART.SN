<?php

namespace App\Application\PublicGeographySource;

use InvalidArgumentException;

final readonly class PublicGeographyBreadcrumbItem
{
    public function __construct(public string $label, public string $url)
    {
        if (trim($label) === '' || filter_var($url, FILTER_VALIDATE_URL) === false) {
            throw new InvalidArgumentException('Invalid public Geography breadcrumb item.');
        }
    }
}
