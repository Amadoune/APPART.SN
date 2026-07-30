<?php

namespace Appart\Modules\Professionals\Domain\Model;

use Appart\Modules\Professionals\Domain\ValueObject\EstablishmentId;
use Appart\Modules\Professionals\Domain\ValueObject\EstablishmentName;
use Appart\Modules\Professionals\Domain\ValueObject\ProfessionalId;
use DateTimeImmutable;

final readonly class Establishment
{
    public function __construct(public EstablishmentId $id, public ProfessionalId $professionalId, public EstablishmentName $name, public DateTimeImmutable $addedAt, public ?DateTimeImmutable $removedAt = null) {}

    public function isActive(): bool
    {
        return $this->removedAt === null;
    }

    public function remove(DateTimeImmutable $at): self
    {
        return new self($this->id, $this->professionalId, $this->name, $this->addedAt, $at);
    }
}
