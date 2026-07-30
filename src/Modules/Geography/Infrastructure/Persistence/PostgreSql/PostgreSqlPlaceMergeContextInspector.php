<?php

namespace Appart\Modules\Geography\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\Geography\Application\PlaceMergeContext\Contract\PlaceMergeContextInspector;
use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeContextInspectionResult;
use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeIntentId;
use Appart\Modules\Geography\Domain\ValueObject\PlaceId;
use Appart\Modules\Geography\Infrastructure\Persistence\PlaceLifecycleWorkflowMapper;
use PDO;
use Throwable;

final readonly class PostgreSqlPlaceMergeContextInspector implements PlaceMergeContextInspector
{
    public function __construct(
        private PDO $connection,
        private PlaceLifecycleWorkflowMapper $mapper,
    ) {}

    public function inspect(
        PlaceId $sourceId,
        PlaceMergeIntentId $intentId,
    ): PlaceMergeContextInspectionResult {
        $statement = $this->connection->prepare(
            'SELECT place_id::text,version,entry_kind,previous_state,current_state,action,
                    target_id::text,target_version,target_state,source_type,target_type,
                    source_country,target_country,actor_id::text,
                    to_char(occurred_at AT TIME ZONE \'UTC\',\'YYYY-MM-DD"T"HH24:MI:SS.US"Z"\') AS occurred_at,
                    intent_id::text,context_version,entry_checksum
             FROM geography.place_lifecycle_transitions
             WHERE place_id=CAST(:place_id AS uuid)
               AND intent_id=CAST(:intent_id AS uuid)',
        );
        $statement->execute([
            'place_id' => $sourceId->value,
            'intent_id' => $intentId->value,
        ]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            return PlaceMergeContextInspectionResult::missing($sourceId, $intentId);
        }

        try {
            return PlaceMergeContextInspectionResult::found(
                $this->mapper->contextInspection($row),
            );
        } catch (Throwable) {
            return PlaceMergeContextInspectionResult::corrupted($sourceId, $intentId);
        }
    }
}
