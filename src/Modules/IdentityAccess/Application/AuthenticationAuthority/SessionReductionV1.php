<?php

namespace Appart\Modules\IdentityAccess\Application\AuthenticationAuthority;

use Appart\Modules\IdentityAccess\Application\IdentityAccessCompletionPersistence\OwnerPersistenceState;
use DateTimeImmutable;

interface SessionReductionV1
{
    public function reduce(?OwnerPersistenceState $state, #[\SensitiveParameter] string $presentedSecret, DateTimeImmutable $observedAt): SessionVerdict;
}
