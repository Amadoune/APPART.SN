<?php

namespace Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\Event;

use DateTimeImmutable;
use InvalidArgumentException;

final readonly class PropertyLifecycleEventInstant
{
    private function __construct(public string $value) {}

    public static function fromCanonicalUtc(string $value): self
    {
        $instant = DateTimeImmutable::createFromFormat('!Y-m-d\TH:i:s.u\Z', $value);
        if ($instant === false || $instant->format('Y-m-d\TH:i:s.u\Z') !== $value) {
            throw new InvalidArgumentException('Property lifecycle event instant must be canonical UTC with microseconds.');
        }

        return new self($value);
    }
}
