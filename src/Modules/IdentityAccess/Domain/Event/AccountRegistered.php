<?php

namespace Appart\Modules\IdentityAccess\Domain\Event;

use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use DateTimeImmutable;

final readonly class AccountRegistered extends AbstractAccountEvent
{
    public function __construct(AccountId $id, DateTimeImmutable $at)
    {
        parent::__construct($id, $at);
    }
}
