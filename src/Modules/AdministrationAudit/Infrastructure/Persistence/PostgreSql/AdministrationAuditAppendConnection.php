<?php

namespace Appart\Modules\AdministrationAudit\Infrastructure\Persistence\PostgreSql;

use PDO;

final readonly class AdministrationAuditAppendConnection
{
    public function __construct(private PDO $connection) {}

    public function pdo(): PDO
    {
        return $this->connection;
    }
}
