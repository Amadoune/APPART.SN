<?php

namespace Appart\Modules\IdentityAccess\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\IdentityAccess\Infrastructure\Persistence\IdentityAccessCompletionPersistenceMapper;
use PDO;

final readonly class PostgreSqlAccountClosureStore extends AbstractPostgreSqlOwnerPersistenceStore
{
    public function __construct(PDO $connection, IdentityAccessCompletionPersistenceMapper $mapper)
    {
        parent::__construct($connection, $mapper, 'account_closures', 'account_id', [
            'state', 'requested_at', 'closed_at', 'reopened_at', 'actor_id', 'reason_category',
            'policy_version', 'cooling_off_until', 'retention_class', 'legal_hold',
            'session_checkpoint', 'updated_at',
        ]);
    }
}
