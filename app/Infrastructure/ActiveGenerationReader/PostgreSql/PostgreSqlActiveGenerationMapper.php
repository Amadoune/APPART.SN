<?php

namespace App\Infrastructure\ActiveGenerationReader\PostgreSql;

use App\Application\PublicProjectionStore\PublicProjectionGeneration;
use App\Application\PublicProjectionStore\PublicProjectionGenerationId;
use App\Application\PublicProjectionStore\PublicProjectionGenerationState;
use RuntimeException;
use Throwable;

final readonly class PostgreSqlActiveGenerationMapper
{
    /** @param array<string, mixed> $row */
    public function toGeneration(array $row): PublicProjectionGeneration
    {
        try {
            $state = PublicProjectionGenerationState::from((string) ($row['state'] ?? ''));
            if ($state !== PublicProjectionGenerationState::Active) {
                throw new RuntimeException('The durable generation is not active.');
            }

            return new PublicProjectionGeneration(
                PublicProjectionGenerationId::fromString((string) ($row['generation_id'] ?? '')),
                $state,
            );
        } catch (Throwable $error) {
            throw new RuntimeException('Corrupt active generation.', 0, $error);
        }
    }
}
