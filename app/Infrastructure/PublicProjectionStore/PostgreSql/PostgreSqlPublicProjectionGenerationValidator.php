<?php

namespace App\Infrastructure\PublicProjectionStore\PostgreSql;

use App\Application\PublicProjectionRebuild\Contract\PublicProjectionGenerationValidator;
use App\Application\PublicProjectionRebuild\PublicProjectionGenerationManifest;
use App\Application\PublicProjectionRebuild\PublicProjectionGenerationValidation;
use App\Application\PublicProjectionStore\PublicProjectionGenerationId;
use App\Application\PublicProjectionStore\PublicProjectionWatermarkRelation;
use PDO;
use RuntimeException;

final readonly class PostgreSqlPublicProjectionGenerationValidator implements PublicProjectionGenerationValidator
{
    public function __construct(private PDO $connection, private PostgreSqlPublicListingProjectionMapper $mapper) {}

    public function validate(PublicProjectionGenerationId $generationId, PublicProjectionGenerationManifest $manifest): PublicProjectionGenerationValidation
    {
        $statement = $this->connection->prepare('SELECT * FROM public_projection.listing_projections WHERE generation_id=:generation ORDER BY listing_id,canonical_path');
        $statement->execute(['generation' => $generationId->value]);
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
        $seen = [];
        $divergent = [];
        $corrupt = [];

        foreach ($rows as $row) {
            $listingId = (string) $row['listing_id'];
            try {
                $record = $this->mapper->toRecord($row);
            } catch (RuntimeException) {
                $corrupt[] = $listingId;

                continue;
            }
            if ($record->state->value !== 'current') {
                continue;
            }
            $seen[$listingId] = true;
            $expected = $manifest->entries[$listingId] ?? null;
            if ($expected === null || $record->watermark->compareTo($expected->watermark) !== PublicProjectionWatermarkRelation::Equal) {
                $divergent[] = $listingId;
            }
        }

        $missing = [];
        foreach ($manifest->entries as $listingId => $entry) {
            if (! isset($seen[$listingId])) {
                $missing[] = $listingId;
            }
        }

        return new PublicProjectionGenerationValidation($generationId, $manifest->count(), count($seen), array_values(array_unique($missing)), array_values(array_unique($divergent)), array_values(array_unique($corrupt)));
    }
}
