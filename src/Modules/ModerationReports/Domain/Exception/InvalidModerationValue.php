<?php

namespace Appart\Modules\ModerationReports\Domain\Exception;

final class InvalidModerationValue extends ModerationException
{
    public static function field(string $field): self
    {
        return new self("Invalid moderation value: {$field}.");
    }
}
