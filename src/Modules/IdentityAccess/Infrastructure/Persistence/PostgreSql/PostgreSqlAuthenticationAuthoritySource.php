<?php

namespace Appart\Modules\IdentityAccess\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\IdentityAccess\Application\AuthenticationAuthority\Contract\CredentialRecord;
use Appart\Modules\IdentityAccess\Application\AuthenticationAuthority\Contract\CredentialSourceV1;
use Appart\Modules\IdentityAccess\Application\AuthenticationAuthority\Contract\LoginIdentitySourceV1;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use PDO;

final readonly class PostgreSqlAuthenticationAuthoritySource implements CredentialSourceV1, LoginIdentitySourceV1
{
    public function __construct(private PDO $connection) {}

    public function byEmail(string $normalizedEmail): ?AccountId
    {
        return $this->identity('email', $normalizedEmail);
    }

    public function byPhone(string $normalizedPhone): ?AccountId
    {
        return $this->identity('phone', $normalizedPhone);
    }

    public function credential(AccountId $accountId): ?CredentialRecord
    {
        $statement = $this->connection->prepare('SELECT c.encoded_password_hash,a.historical_suspended FROM identity_access.accounts a JOIN identity_access.account_credentials c ON c.account_id=a.account_id WHERE a.account_id=CAST(:account_id AS uuid)');
        $statement->execute(['account_id' => $accountId->value]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : new CredentialRecord((string) $row['encoded_password_hash'], ! (bool) $row['historical_suspended']);
    }

    private function identity(string $column, string $value): ?AccountId
    {
        $statement = $this->connection->prepare("SELECT account_id::text FROM identity_access.accounts WHERE {$column}=:identity");
        $statement->execute(['identity' => $value]);
        $found = $statement->fetchColumn();

        return is_string($found) ? AccountId::fromString($found) : null;
    }
}
