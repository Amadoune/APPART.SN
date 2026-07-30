<?php

namespace Appart\Modules\ModerationReports\Domain\Exception;

final class ModerationViolation extends ModerationException
{
    public static function closed(): self
    {
        return new self('The moderation case is closed.');
    }

    public static function duplicate(string $type): self
    {
        return new self("Duplicate {$type} identity.");
    }

    public static function missing(string $type): self
    {
        return new self("Unknown {$type}.");
    }

    public static function alreadyValidated(): self
    {
        return new self('The report is already validated.');
    }

    public static function decisionRequired(): self
    {
        return new self('A decision is required before closing the case.');
    }
}
