<?php

namespace App\Application\ModerationHttpReadBoundaries;

use Appart\Modules\IdentityAccess\Application\ModeratorAuthorization\Contract\ModeratorAuthorizationReaderV1;
use Appart\Modules\IdentityAccess\Application\ModeratorAuthorization\ModerationCapabilityV1;
use Appart\Modules\IdentityAccess\Application\ModeratorAuthorization\ModeratorAuthorizationDecisionV1;
use Appart\Modules\IdentityAccess\Domain\Exception\InvalidIdentityValue;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use Appart\Modules\ModerationReports\Application\HttpReadBoundaries\Contract\ReadModerationDecisionV1;
use Appart\Modules\ModerationReports\Application\HttpReadBoundaries\ModerationDecisionPurposeV1;
use Appart\Modules\ModerationReports\Application\HttpReadBoundaries\ReadModerationDecisionQueryV1;
use Appart\Modules\ModerationReports\Application\HttpReadBoundaries\ReadModerationDecisionResultV1;
use Appart\Modules\ModerationReports\Application\HttpReadBoundaries\ReadStatusV1;
use Appart\Modules\ModerationReports\Application\ModerationPersistence\Contract\ModerationDecisionStore;
use Appart\Modules\ModerationReports\Application\ModerationPersistence\ModerationPersistenceReadStatus;
use Appart\Modules\ModerationReports\Infrastructure\ReadModel\HttpReadBoundaries\ModerationDecisionViewMapperV1;

final readonly class OwnerReadModerationDecisionV1 implements ReadModerationDecisionV1
{
    public function __construct(
        private ModeratorAuthorizationReaderV1 $authorization,
        private ModerationDecisionStore $decisions,
        private ModerationDecisionViewMapperV1 $mapper,
    ) {}

    public function read(ReadModerationDecisionQueryV1 $query): ReadModerationDecisionResultV1
    {
        try {
            $authorization = $this->authorization->authorize(
                AccountId::fromString($query->actorAccountId),
                $query->purpose === ModerationDecisionPurposeV1::Audit
                    ? ModerationCapabilityV1::Audit
                    : ModerationCapabilityV1::Decide,
                $query->observedAt,
            );
        } catch (InvalidIdentityValue) {
            return ReadModerationDecisionResultV1::status(ReadStatusV1::ForbiddenActor);
        }
        if ($authorization !== ModeratorAuthorizationDecisionV1::Allowed) {
            return ReadModerationDecisionResultV1::status(
                $authorization === ModeratorAuthorizationDecisionV1::DependencyUnavailable
                    ? ReadStatusV1::DependencyUnavailable
                    : ReadStatusV1::ForbiddenActor,
            );
        }

        $result = $query->decisionId === null
            ? $this->decisions->readCurrent($query->caseId)
            : $this->decisions->read($query->caseId, $query->decisionId);

        return match ($result->status) {
            ModerationPersistenceReadStatus::Found => $result->decision === null
                ? ReadModerationDecisionResultV1::status(ReadStatusV1::Corrupted)
                : ReadModerationDecisionResultV1::found($this->mapper->map($result->decision)),
            ModerationPersistenceReadStatus::Missing => ReadModerationDecisionResultV1::status(ReadStatusV1::Missing),
            ModerationPersistenceReadStatus::Corrupted => ReadModerationDecisionResultV1::status(ReadStatusV1::Corrupted),
            ModerationPersistenceReadStatus::DependencyUnavailable => ReadModerationDecisionResultV1::status(ReadStatusV1::DependencyUnavailable),
        };
    }
}
