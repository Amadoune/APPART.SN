<?php

namespace Appart\Modules\ModerationReports\Application\UseCase;

use Appart\Modules\ModerationReports\Application\Contract\ModerationCaseRegistry;
use Appart\Modules\ModerationReports\Domain\Policy\ModerationDecisionPolicy;
use Appart\Modules\ModerationReports\Domain\ValueObject\DecisionId;
use Appart\Modules\ModerationReports\Domain\ValueObject\DecisionType;
use Appart\Modules\ModerationReports\Domain\ValueObject\FindingId;
use Appart\Modules\ModerationReports\Domain\ValueObject\ModerationCaseId;
use Appart\Modules\ModerationReports\Domain\ValueObject\ModerationRationale;
use Appart\Modules\ModerationReports\Domain\ValueObject\ModeratorId;
use DateTimeImmutable;

final readonly class IssueDecision extends ModerationCaseUseCase
{
    public function __construct(ModerationCaseRegistry $cases, private ModerationDecisionPolicy $policy)
    {
        parent::__construct($cases);
    }

    /** @param list<FindingId> $findingIds */
    public function execute(ModerationCaseId $caseId, DecisionId $id, array $findingIds, DecisionType $type, ModerationRationale $rationale, ModeratorId $moderator, ?DecisionId $supersedes, DateTimeImmutable $at): void
    {
        [$case,$version] = $this->load($caseId);
        $case->issueDecision($id, $findingIds, $type, $rationale, $moderator, $supersedes, $at, $this->policy);
        $this->cases->saveWithDecisionReservation($case, $id, $version);
    }
}
