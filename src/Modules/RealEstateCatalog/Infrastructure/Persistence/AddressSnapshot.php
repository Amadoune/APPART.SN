<?php

namespace Appart\Modules\RealEstateCatalog\Infrastructure\Persistence;

final readonly class AddressSnapshot
{
    public function __construct(public string $id, public string $placeId, public string $line) {}
}
