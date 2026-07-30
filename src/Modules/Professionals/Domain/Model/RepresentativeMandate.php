<?php

namespace Appart\Modules\Professionals\Domain\Model;

use Appart\Modules\Professionals\Domain\ValueObject\EstablishmentId;
use Appart\Modules\Professionals\Domain\ValueObject\MandateId;
use Appart\Modules\Professionals\Domain\ValueObject\MandateRole;
use Appart\Modules\Professionals\Domain\ValueObject\RepresentativeId;
use DateTimeImmutable;

final readonly class RepresentativeMandate
{
    public function __construct(public MandateId $id, public EstablishmentId $establishmentId, public RepresentativeId $representativeId, public MandateRole $role, public DateTimeImmutable $grantedAt, public ?DateTimeImmutable $revokedAt = null) {}

    public function isActive(): bool
    {
        return $this->revokedAt === null;
    }

    public function revoke(DateTimeImmutable $at): self
    {
        return new self($this->id, $this->establishmentId, $this->representativeId, $this->role, $this->grantedAt, $at);
    }
}
