<?php

namespace App\Application\PublicGeographyRefresh;

final readonly class AffectedPublicGeographyTerminalPage
{
    /** @param list<string> $terminalPlaceIds */
    public function __construct(public AffectedPublicGeographyTerminalStatus $status, public array $terminalPlaceIds = [], public ?string $nextCursor = null) {}
}
