<?php

namespace Appart\Modules\IdentityAccess\Domain\Event;

use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use Appart\Modules\IdentityAccess\Domain\ValueObject\VerificationChannel;
use DateTimeImmutable;

final readonly class VerificationReplaced extends AbstractAccountEvent
{
    public function __construct(AccountId $id, public VerificationChannel $channel, DateTimeImmutable $at)
    {
        parent::__construct($id, $at);
    }
}
