<?php

namespace Appart\Modules\ModerationReports\Application\HttpReadBoundaries;

final readonly class ReadModerationCaseResultV1
{
    private function __construct(
        public ReadStatusV1 $status,
        public ?ModerationCaseViewV1 $case,
    ) {}

    public static function found(ModerationCaseViewV1 $case): self
    {
        return new self(ReadStatusV1::Found, $case);
    }

    public static function status(ReadStatusV1 $status): self
    {
        return new self($status, null);
    }
}
