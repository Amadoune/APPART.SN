<?php

namespace Appart\Modules\IdentityAccess\Application\AccountStatusPersistence\Contract;

use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusContextV1;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusTransition;
use Appart\Modules\IdentityAccess\Application\AccountStatusPersistence\AccountStatusPersistenceReadResult;
use Appart\Modules\IdentityAccess\Application\AccountStatusPersistence\AccountStatusPersistenceWriteResult;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;

interface AccountStatusWorkflowStore
{
    public function read(AccountId $accountId): AccountStatusPersistenceReadResult;

    public function bootstrap(AccountId $accountId): AccountStatusPersistenceWriteResult;

    public function append(
        AccountStatusTransition $transition,
        AccountStatusContextV1 $context,
    ): AccountStatusPersistenceWriteResult;
}
