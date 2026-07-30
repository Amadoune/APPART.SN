<?php

namespace App\Application\ModerationHttpReadBoundaries;

use Appart\Modules\IdentityAccess\Application\ModeratorAuthorization\Contract\ModeratorAuthorizationReaderV1;
use Appart\Modules\IdentityAccess\Application\ModeratorAuthorization\ModerationCapabilityV1;
use Appart\Modules\IdentityAccess\Application\ModeratorAuthorization\ModeratorAuthorizationDecisionV1;
use Appart\Modules\IdentityAccess\Domain\Exception\InvalidIdentityValue;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use Appart\Modules\ModerationReports\Application\HttpReadBoundaries\Contract\ReadModerationCaseV1;
use Appart\Modules\ModerationReports\Application\HttpReadBoundaries\ModerationCaseViewLevelV1;
use Appart\Modules\ModerationReports\Application\HttpReadBoundaries\ReadModerationCaseQueryV1;
use Appart\Modules\ModerationReports\Application\HttpReadBoundaries\ReadModerationCaseResultV1;
use Appart\Modules\ModerationReports\Application\HttpReadBoundaries\ReadStatusV1;
use Appart\Modules\ModerationReports\Application\ModerationPersistence\Contract\ModerationCaseStore;
use Appart\Modules\ModerationReports\Application\ModerationPersistence\ModerationPersistenceReadStatus;
use Appart\Modules\ModerationReports\Infrastructure\ReadModel\HttpReadBoundaries\ModerationCaseViewMapperV1;

final readonly class OwnerReadModerationCaseV1 implements ReadModerationCaseV1
{
    public function __construct(
        private ModeratorAuthorizationReaderV1 $authorization,
        private ModerationCaseStore $cases,
        private ModerationCaseViewMapperV1 $mapper,
    ) {}

    public function read(ReadModerationCaseQueryV1 $query): ReadModerationCaseResultV1
    {
        try {
            $decision = $this->authorization->authorize(
                AccountId::fromString($query->actorAccountId),
                $query->viewLevel === ModerationCaseViewLevelV1::Audit
                    ? ModerationCapabilityV1::Audit
                    : ModerationCapabilityV1::Investigate,
                $query->observedAt,
            );
        } catch (InvalidIdentityValue) {
            return ReadModerationCaseResultV1::status(ReadStatusV1::ForbiddenActor);
        }
        if ($decision !== ModeratorAuthorizationDecisionV1::Allowed) {
            return ReadModerationCaseResultV1::status(
                $decision === ModeratorAuthorizationDecisionV1::DependencyUnavailable
                    ? ReadStatusV1::DependencyUnavailable
                    : ReadStatusV1::ForbiddenActor,
            );
        }

        $result = $this->cases->read($query->caseId);

        return match ($result->status) {
            ModerationPersistenceReadStatus::Found => $result->state === null
                ? ReadModerationCaseResultV1::status(ReadStatusV1::Corrupted)
                : ReadModerationCaseResultV1::found($this->mapper->map($result->state)),
            ModerationPersistenceReadStatus::Missing => ReadModerationCaseResultV1::status(ReadStatusV1::Missing),
            ModerationPersistenceReadStatus::Corrupted => ReadModerationCaseResultV1::status(ReadStatusV1::Corrupted),
            ModerationPersistenceReadStatus::DependencyUnavailable => ReadModerationCaseResultV1::status(ReadStatusV1::DependencyUnavailable),
        };
    }
}
