<?php

namespace Appart\Modules\AdministrationAudit\Domain\Model;

use Appart\Modules\AdministrationAudit\Domain\ValueObject\ActorId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\ApprovalId;
use DateTimeImmutable;

final readonly class Approval
{
    public function __construct(public ApprovalId $id, public ActorId $approverId, public DateTimeImmutable $approvedAt) {}
}
