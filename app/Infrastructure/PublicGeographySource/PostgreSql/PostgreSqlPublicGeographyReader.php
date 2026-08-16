<?php

namespace App\Infrastructure\PublicGeographySource\PostgreSql;

use App\Application\PublicGeographyRevision\Contract\PublicGeographyRevisionReader;
use App\Application\PublicGeographyRevision\PublicGeographyRevision;
use App\Application\PublicGeographySource\Contract\PublicGeographyDecisionReader;
use App\Application\PublicGeographySource\PublicGeographyDecisionV2;
use App\Application\PublicGeographySource\PublicGeographyReadResult;
use App\Application\PublicGeographySource\PublicGeographyReadStatus;
use PDO;

final readonly class PostgreSqlPublicGeographyReader implements PublicGeographyDecisionReader, PublicGeographyRevisionReader
{
    public function __construct(private PDO $connection, private PostgreSqlPublicGeographyMapper $mapper) {}

    public function read(string $placeId): PublicGeographyReadResult
    {
        $s = $this->connection->prepare('SELECT place_id,version,causation_key,revision_checksum,payload::text AS payload,payload_checksum FROM public_geography.decisions WHERE place_id=:place_id');
        $s->execute(['place_id' => $placeId]);
        $row = $s->fetch(PDO::FETCH_ASSOC);
        if ($row === false) {
            return PublicGeographyReadResult::missing($placeId);
        } try {
            $decision = $this->mapper->toDecision($row);

            return $decision instanceof PublicGeographyDecisionV2
                ? PublicGeographyReadResult::foundV2($placeId, $decision)
                : PublicGeographyReadResult::found($placeId, $decision);
        } catch (\Throwable) {
            return PublicGeographyReadResult::corrupted($placeId);
        }
    }

    public function stableRevisionForPlace(string $placeId): ?PublicGeographyRevision
    {
        $result = $this->read($placeId);

        return $result->status === PublicGeographyReadStatus::Found ? $result->decision?->revision : null;
    }
}
