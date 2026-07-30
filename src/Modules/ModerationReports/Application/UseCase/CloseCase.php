<?php

namespace Appart\Modules\ModerationReports\Application\UseCase;

use Appart\Modules\ModerationReports\Application\Contract\ModerationCaseRegistry;
use Appart\Modules\ModerationReports\Domain\Policy\ModerationCaseClosurePolicy;
use Appart\Modules\ModerationReports\Domain\ValueObject\ModerationCaseId;
use Appart\Modules\ModerationReports\Domain\ValueObject\ModerationRationale;
use Appart\Modules\ModerationReports\Domain\ValueObject\ModeratorId;
use DateTimeImmutable;

final readonly class CloseCase extends ModerationCaseUseCase
{
    public function __construct(ModerationCaseRegistry $cases, private ModerationCaseClosurePolicy $policy)
    {
        parent::__construct($cases);
    }

    public function execute(ModerationCaseId $caseId, ModeratorId $moderator, ModerationRationale $rationale, DateTimeImmutable $at): void
    {
        [$case,$version] = $this->load($caseId);
        $case->close($moderator, $rationale, $at, $this->policy);
        $this->cases->save($case, $version);
    }
}
