<?php

namespace Appart\Modules\ContactsLeads\Infrastructure\Persistence\PostgreSql;

use PDO;

final readonly class AntiAbuseOwnerSourceConnection
{
    public function __construct(public PDO $connection) {}
}
