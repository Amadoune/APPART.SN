<?php

namespace Appart\Modules\AdministrationAudit\Application\AdministrativeActionHistoricalMirror;

use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleState;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionPersistenceCoexistence\AdministrativeActionLifecycleEnrollmentCheckpoint;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionPersistenceCoexistence\AdministrativeActionLifecycleSourceChecksum;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\AdministrativeActionId;

final readonly class AdministrativeActionEnrollmentCanonicalizer
{
    public function canonicalSource(
        AdministrativeActionId $actionId,
        int $historicalVersion,
        AdministrativeActionLifecycleState $state,
    ): string {
        return implode("\n", [
            AdministrativeActionEnrollmentCanonicalVersion::V1->value,
            $actionId->value,
            (string) $historicalVersion,
            $state->value,
        ]);
    }

    public function checkpoint(
        AdministrativeActionId $actionId,
        int $historicalVersion,
        AdministrativeActionLifecycleState $state,
    ): AdministrativeActionLifecycleEnrollmentCheckpoint {
        return new AdministrativeActionLifecycleEnrollmentCheckpoint(
            $actionId,
            $historicalVersion,
            $state,
            AdministrativeActionLifecycleSourceChecksum::fromString(
                hash('sha256', $this->canonicalSource($actionId, $historicalVersion, $state)),
            ),
        );
    }
}
