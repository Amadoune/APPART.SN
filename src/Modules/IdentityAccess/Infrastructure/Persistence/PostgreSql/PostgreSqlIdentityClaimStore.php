<?php

namespace Appart\Modules\IdentityAccess\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\IdentityAccess\Infrastructure\Persistence\IdentityAccessCompletionPersistenceMapper;
use PDO;

final readonly class PostgreSqlIdentityClaimStore extends AbstractPostgreSqlOwnerPersistenceStore
{
    public function __construct(PDO $connection, IdentityAccessCompletionPersistenceMapper $mapper)
    {
        parent::__construct($connection, $mapper, 'identity_claims', 'claim_id', [
            'account_id', 'claim_type', 'claim_ciphertext', 'claim_fingerprint', 'normalization_version',
            'state', 'reserved_at', 'activated_at', 'ended_at', 'contact_change_id',
        ]);
    }
}
