<?php

namespace Appart\Modules\IdentityAccess\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\IdentityAccess\Infrastructure\Persistence\IdentityAccessCompletionPersistenceMapper;
use PDO;

final readonly class PostgreSqlPendingContactChangeStore extends AbstractPostgreSqlOwnerPersistenceStore
{
    public function __construct(PDO $connection, IdentityAccessCompletionPersistenceMapper $mapper)
    {
        parent::__construct($connection, $mapper, 'pending_contact_changes', 'change_id', [
            'account_id', 'contact_type', 'target_ciphertext', 'target_fingerprint', 'challenge_hash',
            'claim_id', 'state', 'requested_at', 'expires_at', 'verified_at', 'activated_at',
            'fresh_auth_evidence', 'policy_version', 'normalization_version',
        ]);
    }
}
