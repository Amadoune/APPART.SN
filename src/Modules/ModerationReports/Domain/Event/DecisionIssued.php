<?php

namespace Appart\Modules\ModerationReports\Domain\Event;

use Appart\Modules\ModerationReports\Domain\ValueObject\DecisionId;
use Appart\Modules\ModerationReports\Domain\ValueObject\DecisionType;
use Appart\Modules\ModerationReports\Domain\ValueObject\FindingId;
use Appart\Modules\ModerationReports\Domain\ValueObject\ListingId;
use Appart\Modules\ModerationReports\Domain\ValueObject\ModerationCaseId;
use Appart\Modules\ModerationReports\Domain\ValueObject\ModerationRationale;
use Appart\Modules\ModerationReports\Domain\ValueObject\ModeratorId;
use DateTimeImmutable;

final readonly class DecisionIssued extends AbstractModerationCaseEvent
{
    /** @param non-empty-list<FindingId> $findingIds */
    public function __construct(ModerationCaseId $caseId, ListingId $listingId, public DecisionId $decisionId, public array $findingIds, public DecisionType $type, public ModerationRationale $rationale, public ModeratorId $issuedBy, public ?DecisionId $supersedesDecisionId, DateTimeImmutable $at)
    {
        parent::__construct($caseId, $listingId, $at);
    }
}
