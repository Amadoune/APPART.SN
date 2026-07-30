<?php

namespace Appart\Modules\IdentityAccess\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\IdentityAccess\Application\AccountAvailability\AccountClosureReadResult;
use Appart\Modules\IdentityAccess\Application\AccountAvailability\AccountClosureState;
use Appart\Modules\IdentityAccess\Application\AccountAvailability\Contract\AccountClosureStateReader;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use PDO;
use Throwable;

final readonly class PostgreSqlAccountClosureStateReader implements AccountClosureStateReader
{
    public function __construct(private PDO $connection) {}

    public function read(AccountId $accountId): AccountClosureReadResult
    {
        try {
            $statement = $this->connection->prepare(
                'SELECT state,version FROM identity_access_completion.account_closures
                 WHERE account_id=CAST(:account_id AS uuid)',
            );
            $statement->execute(['account_id' => $accountId->value]);
            $row = $statement->fetch(PDO::FETCH_ASSOC);
            if ($row === false) {
                return AccountClosureReadResult::legacyOpen();
            }
            $state = $row['state'] ?? null;
            $version = $row['version'] ?? null;
            if (! is_string($state) || ! is_numeric($version) || (int) $version < 1) {
                return AccountClosureReadResult::persistenceRejected();
            }

            return AccountClosureReadResult::found(AccountClosureState::from($state), (int) $version);
        } catch (Throwable) {
            return AccountClosureReadResult::persistenceRejected();
        }
    }
}
