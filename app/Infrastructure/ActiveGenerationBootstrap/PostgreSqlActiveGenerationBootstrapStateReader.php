<?php

namespace App\Infrastructure\ActiveGenerationBootstrap;

use App\Application\ActiveGenerationBootstrap\ActiveGenerationBootstrapState;
use App\Application\ActiveGenerationBootstrap\Contract\ActiveGenerationBootstrapStateReader;
use PDO;

final readonly class PostgreSqlActiveGenerationBootstrapStateReader implements ActiveGenerationBootstrapStateReader
{
    public function __construct(private PDO $connection) {}

    public function read(): ActiveGenerationBootstrapState
    {
        $generations = (int) $this->connection->query('SELECT count(*) FROM public_projection.generations')->fetchColumn();
        $active = (int) $this->connection->query("SELECT count(*) FROM public_projection.generations WHERE state='active'")->fetchColumn();
        $projections = (int) $this->connection->query('SELECT count(*) FROM public_projection.listing_projections')->fetchColumn();
        $activeId = $this->connection->query("SELECT generation_id::text FROM public_projection.generations WHERE state='active' ORDER BY generation_id LIMIT 1")->fetchColumn();
        $candidateIds = $this->connection->query("SELECT generation_id::text FROM public_projection.generations WHERE state='candidate' ORDER BY generation_id")->fetchAll(PDO::FETCH_COLUMN);

        return new ActiveGenerationBootstrapState(
            $generations,
            $active,
            $projections,
            is_string($activeId) ? $activeId : null,
            array_values(array_filter($candidateIds, 'is_string')),
        );
    }
}
