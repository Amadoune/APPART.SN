<?php

namespace Appart\Modules\ModerationReports\Application\UseCase;

use Appart\Modules\ModerationReports\Application\Contract\ModerationCaseRegistry;
use Appart\Modules\ModerationReports\Domain\Exception\ModerationCaseNotFound;
use Appart\Modules\ModerationReports\Domain\Model\ModerationCase;
use Appart\Modules\ModerationReports\Domain\ValueObject\ModerationCaseId;

abstract readonly class ModerationCaseUseCase
{
    public function __construct(protected ModerationCaseRegistry $cases) {}

    /** @return array{ModerationCase,int} */
    protected function load(ModerationCaseId $id): array
    {
        $case = $this->cases->find($id) ?? throw new ModerationCaseNotFound;

        return [$case, $case->version()];
    }
}
