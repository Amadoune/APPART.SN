<?php

namespace App\Infrastructure\PublicGeographySource\PostgreSql;

use App\Application\PublicGeographySource\Contract\PublicGeographyDecisionWriter;
use App\Application\PublicGeographySource\PublicGeographyDecision;
use App\Application\PublicGeographySource\PublicGeographyDecisionV2;
use App\Application\PublicGeographySource\PublicGeographyWriteResult;
use PDO;

final readonly class PostgreSqlPublicGeographyWriter implements PublicGeographyDecisionWriter
{
    public function __construct(private PDO $connection, private PostgreSqlPublicGeographyMapper $mapper) {}

    public function store(PublicGeographyDecision|PublicGeographyDecisionV2 $decision): PublicGeographyWriteResult
    {
        $owner = ! $this->connection->inTransaction();
        if ($owner) {
            $this->connection->beginTransaction();
        }
        try {
            $p = $this->mapper->parameters($decision);
            $s = $this->connection->prepare('SELECT pg_advisory_xact_lock(hashtextextended(:place_id,0))');
            $placeId = $decision instanceof PublicGeographyDecisionV2 ? $decision->terminalPlaceId : $decision->placeId;
            $s->execute(['place_id' => $placeId]);
            $s = $this->connection->prepare('SELECT version,revision_checksum,causation_key FROM public_geography.decisions WHERE place_id=:place_id FOR UPDATE');
            $s->execute(['place_id' => $placeId]);
            $current = $s->fetch(PDO::FETCH_ASSOC);
            if ($current !== false && $decision->revision->version->value < (int) $current['version']) {
                return $this->finish(PublicGeographyWriteResult::RejectedObsolete, $owner);
            }
            if ($current !== false && $decision->revision->version->value === (int) $current['version']) {
                $same = hash_equals((string) $current['revision_checksum'], $p['revision_checksum']) && (string) $current['causation_key'] === $p['causation'];

                return $this->finish($same ? PublicGeographyWriteResult::AlreadyApplied : PublicGeographyWriteResult::Divergent, $owner);
            }
            $s = $this->connection->prepare('INSERT INTO public_geography.decisions(place_id,version,causation_key,revision_checksum,payload,payload_checksum) VALUES(:place_id,:version,:causation,:revision_checksum,CAST(:payload AS jsonb),:payload_checksum) ON CONFLICT(place_id) DO UPDATE SET version=EXCLUDED.version,causation_key=EXCLUDED.causation_key,revision_checksum=EXCLUDED.revision_checksum,payload=EXCLUDED.payload,payload_checksum=EXCLUDED.payload_checksum,updated_at=clock_timestamp()');
            $s->execute($p);

            return $this->finish(PublicGeographyWriteResult::Applied, $owner);
        } catch (\Throwable $e) {
            if ($owner) {
                $this->connection->rollBack();
            }

            throw $e;
        }
    }

    private function finish(PublicGeographyWriteResult $result, bool $owner): PublicGeographyWriteResult
    {
        if ($owner) {
            $this->connection->commit();
        }

        return $result;
    }
}
