<?php

namespace Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityPublicRead;

use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;

final readonly class ReservationAvailabilityWindow
{
    public DateTimeImmutable $startsAt;

    public DateTimeImmutable $endsAt;

    public function __construct(DateTimeImmutable $startsAt, DateTimeImmutable $endsAt)
    {
        $utc = new DateTimeZone('UTC');
        $this->startsAt = $startsAt->setTimezone($utc);
        $this->endsAt = $endsAt->setTimezone($utc);

        if ($this->startsAt >= $this->endsAt) {
            throw new InvalidArgumentException('The reservation availability window must have a strictly positive duration.');
        }
    }

    public function canonical(): string
    {
        return $this->startsAt->format('Y-m-d\TH:i:s.u\Z').'/'.$this->endsAt->format('Y-m-d\TH:i:s.u\Z');
    }
}
