<?php

namespace Appart\Modules\ModerationReports\Infrastructure\ReadModel\HttpReadBoundaries;

use Appart\Modules\ModerationReports\Application\HttpReadBoundaries\ModerationDecisionViewV1;
use Appart\Modules\ModerationReports\Application\ModerationPersistence\ModerationPersistenceRecord;

final readonly class ModerationDecisionViewMapperV1
{
    public function map(ModerationPersistenceRecord $decision): ModerationDecisionViewV1
    {
        $attributes = [];
        foreach (['disposition', 'targetAction', 'policyVersion', 'applicationStatus', 'supersededDecisionId'] as $key) {
            $value = $decision->payload[$key] ?? null;
            if (is_scalar($value) || $value === null) {
                $attributes[$key] = $value;
            }
        }

        return new ModerationDecisionViewV1($decision->id, $attributes, $decision->recordedAt);
    }
}
