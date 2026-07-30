<?php

namespace Appart\Modules\ModerationReports\Application\HttpReadBoundaries;

final readonly class ReadModerationDecisionResultV1
{
    private function __construct(
        public ReadStatusV1 $status,
        public ?ModerationDecisionViewV1 $decision,
    ) {}

    public static function found(ModerationDecisionViewV1 $decision): self
    {
        return new self(ReadStatusV1::Found, $decision);
    }

    public static function status(ReadStatusV1 $status): self
    {
        return new self($status, null);
    }
}
