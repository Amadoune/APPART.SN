<?php

namespace Appart\Modules\IdentityAccess\Domain\Event;

use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use Appart\Modules\IdentityAccess\Domain\ValueObject\RoleId;
use DateTimeImmutable;

final readonly class RoleRevoked extends AbstractAccountEvent
{
    public function __construct(AccountId $id, public RoleId $roleId, DateTimeImmutable $at)
    {
        parent::__construct($id, $at);
    }
}
