<?php

namespace Appart\Modules\Geography\Infrastructure\Persistence;

final readonly class PlaceAliasSnapshot
{
    public function __construct(public string $name, public string $recordedAt) {}
}
