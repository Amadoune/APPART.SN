<?php

namespace Appart\Modules\Media\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\Media\Application\Attachment\Contract\MediaAttachmentTransaction;
use Closure;
use PDO;
use Throwable;

final readonly class PostgreSqlMediaAttachmentTransaction implements MediaAttachmentTransaction
{
    public function __construct(private PDO $connection) {}

    public function run(Closure $operation): mixed
    {
        $external = $this->connection->inTransaction();
        if ($external) {
            $this->connection->exec('SAVEPOINT media_ready_asset_attachment');
        } else {
            $this->connection->beginTransaction();
        }
        try {
            $result = $operation();
            if ($external) {
                $this->connection->exec('RELEASE SAVEPOINT media_ready_asset_attachment');
            } else {
                $this->connection->commit();
            }

            return $result;
        } catch (Throwable $error) {
            if ($external && $this->connection->inTransaction()) {
                $this->connection->exec('ROLLBACK TO SAVEPOINT media_ready_asset_attachment');
            } elseif ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }
            throw $error;
        }
    }
}
