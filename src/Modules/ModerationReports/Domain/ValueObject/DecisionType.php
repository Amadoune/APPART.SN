<?php

namespace Appart\Modules\ModerationReports\Domain\ValueObject;

enum DecisionType: string
{
    case NoAction = 'no_action';
    case RecommendChanges = 'recommend_changes';
    case RecommendSuspension = 'recommend_suspension';
    case RecommendRejection = 'recommend_rejection';
    case Escalate = 'escalate';
}
