<?php

namespace App\Infrastructure\ActiveGenerationReader\PostgreSql;

use App\Application\ActiveGenerationReader\ActiveGenerationReadResult;
use App\Application\ActiveGenerationReader\Contract\ActiveGenerationReader;
use PDO;
use Throwable;

final readonly class PostgreSqlActiveGenerationReader implements ActiveGenerationReader
{
    public function __construct(private PDO $connection, private PostgreSqlActiveGenerationMapper $mapper) {}

    public function read(): ActiveGenerationReadResult
    {
        $statement = $this->connection->query("SELECT generation_id,state FROM public_projection.generations WHERE state='active' ORDER BY generation_id LIMIT 2");
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        if ($rows === []) {
            return ActiveGenerationReadResult::missing();
        }
        if (count($rows) !== 1) {
            return ActiveGenerationReadResult::corrupted();
        }

        try {
            return ActiveGenerationReadResult::found($this->mapper->toGeneration($rows[0]));
        } catch (Throwable) {
            return ActiveGenerationReadResult::corrupted();
        }
    }
}
