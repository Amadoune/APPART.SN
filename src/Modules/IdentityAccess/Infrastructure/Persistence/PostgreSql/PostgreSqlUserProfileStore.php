<?php

namespace Appart\Modules\IdentityAccess\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\IdentityAccess\Infrastructure\Persistence\IdentityAccessCompletionPersistenceMapper;
use PDO;

final readonly class PostgreSqlUserProfileStore extends AbstractPostgreSqlOwnerPersistenceStore
{
    public function __construct(PDO $connection, IdentityAccessCompletionPersistenceMapper $mapper)
    {
        parent::__construct($connection, $mapper, 'user_profiles', 'account_id', [
            'display_name_ciphertext', 'email_ciphertext', 'email_fingerprint', 'phone_ciphertext',
            'phone_fingerprint', 'normalization_version', 'enrolled_at', 'updated_at',
        ]);
    }
}
