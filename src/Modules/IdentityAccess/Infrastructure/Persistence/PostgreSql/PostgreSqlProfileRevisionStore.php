<?php

namespace Appart\Modules\IdentityAccess\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\IdentityAccess\Infrastructure\Persistence\IdentityAccessCompletionPersistenceMapper;
use PDO;

final readonly class PostgreSqlProfileRevisionStore extends AbstractPostgreSqlOwnerPersistenceStore
{
    public function __construct(PDO $connection, IdentityAccessCompletionPersistenceMapper $mapper)
    {
        parent::__construct($connection, $mapper, 'profile_revisions', 'revision_id', [
            'account_id', 'revision_type', 'actor_id', 'occurred_at',
            'source_change_id', 'old_value_ciphertext', 'new_value_ciphertext', 'policy_version',
            'normalization_version',
        ], 'source_intent_id', 'revision_checksum', 'profile_version', true);
    }
}
