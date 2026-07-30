<?php

namespace Appart\Modules\ModerationReports\Domain\Model;

use Appart\Modules\ModerationReports\Domain\ValueObject\DecisionId;
use Appart\Modules\ModerationReports\Domain\ValueObject\DecisionType;
use Appart\Modules\ModerationReports\Domain\ValueObject\FindingId;
use Appart\Modules\ModerationReports\Domain\ValueObject\ModerationRationale;
use Appart\Modules\ModerationReports\Domain\ValueObject\ModeratorId;
use DateTimeImmutable;

final readonly class ModerationDecision
{
    /** @param non-empty-list<FindingId> $findingIds */
    public function __construct(public DecisionId $id, public array $findingIds, public DecisionType $type, public ModerationRationale $rationale, public ModeratorId $issuedBy, public DateTimeImmutable $issuedAt, public ?DecisionId $supersedesDecisionId) {}

    public function isFinal(): bool
    {
        return $this->type !== DecisionType::Escalate;
    }
}
