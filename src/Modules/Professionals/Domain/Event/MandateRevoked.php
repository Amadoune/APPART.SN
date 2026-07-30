<?php

namespace Appart\Modules\Professionals\Domain\Event;

use Appart\Modules\Professionals\Domain\ValueObject\MandateId;
use Appart\Modules\Professionals\Domain\ValueObject\ProfessionalId;
use DateTimeImmutable;

final readonly class MandateRevoked extends AbstractProfessionalEvent
{
    public function __construct(ProfessionalId $id, public MandateId $mandateId, DateTimeImmutable $at)
    {
        parent::__construct($id, $at);
    }
}
