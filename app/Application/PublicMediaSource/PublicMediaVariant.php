<?php

namespace App\Application\PublicMediaSource;

use InvalidArgumentException;

final readonly class PublicMediaVariant
{
    public function __construct(public string $name, public string $url)
    {
        if (trim($name) === '' || filter_var($url, FILTER_VALIDATE_URL) === false) {
            throw new InvalidArgumentException('Invalid public Media variant.');
        }
    }
}
