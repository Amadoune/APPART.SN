<?php

namespace Appart\Modules\IdentityAccess\Domain\Event;

use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use Appart\Modules\IdentityAccess\Domain\ValueObject\ConsentPurpose;
use DateTimeImmutable;

final readonly class ConsentGranted extends AbstractAccountEvent
{
    public function __construct(AccountId $id, public ConsentPurpose $purpose, DateTimeImmutable $at)
    {
        parent::__construct($id, $at);
    }
}
