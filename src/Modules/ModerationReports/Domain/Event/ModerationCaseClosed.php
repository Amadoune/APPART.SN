<?php

namespace Appart\Modules\ModerationReports\Domain\Event;

use Appart\Modules\ModerationReports\Domain\ValueObject\DecisionId;
use Appart\Modules\ModerationReports\Domain\ValueObject\ListingId;
use Appart\Modules\ModerationReports\Domain\ValueObject\ModerationCaseId;
use Appart\Modules\ModerationReports\Domain\ValueObject\ModerationRationale;
use Appart\Modules\ModerationReports\Domain\ValueObject\ModeratorId;
use DateTimeImmutable;

final readonly class ModerationCaseClosed extends AbstractModerationCaseEvent
{
    public function __construct(ModerationCaseId $caseId, ListingId $listingId, public DecisionId $finalDecisionId, public ModeratorId $closedBy, public ModerationRationale $rationale, DateTimeImmutable $at)
    {
        parent::__construct($caseId, $listingId, $at);
    }
}
