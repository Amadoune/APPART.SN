<?php

namespace Appart\Modules\Professionals\Infrastructure\Runtime;

use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusState;
use Appart\Modules\Professionals\Application\ProfessionalStatusPersistence\Contract\ProfessionalStatusWorkflowStore;
use Appart\Modules\Professionals\Application\ProfessionalStatusPersistence\ProfessionalStatusId;
use Appart\Modules\Professionals\Application\ProfessionalStatusPersistence\ProfessionalStatusPersistenceReadStatus;
use Appart\Modules\Professionals\Application\ProfessionalStatusPublicRead\Contract\ProfessionalPublicStatusReaderV1;
use Appart\Modules\Professionals\Application\ProfessionalStatusPublicRead\ProfessionalPublicStatusDecisionV1;
use Appart\Modules\Professionals\Domain\ValueObject\ProfessionalId;
use Throwable;

final readonly class OwnerProfessionalPublicStatusReaderV1 implements ProfessionalPublicStatusReaderV1
{
    public function __construct(private ProfessionalStatusWorkflowStore $store) {}

    public function read(ProfessionalId $professionalId): ProfessionalPublicStatusDecisionV1
    {
        try {
            $result = $this->store->read(ProfessionalStatusId::fromString($professionalId->value));

            return match ($result->status) {
                ProfessionalStatusPersistenceReadStatus::Missing => ProfessionalPublicStatusDecisionV1::Missing,
                ProfessionalStatusPersistenceReadStatus::Corrupted => ProfessionalPublicStatusDecisionV1::Corrupted,
                ProfessionalStatusPersistenceReadStatus::Found => match ($result->snapshot?->state) {
                    ProfessionalStatusState::Active => ProfessionalPublicStatusDecisionV1::Available,
                    ProfessionalStatusState::Suspended => ProfessionalPublicStatusDecisionV1::Unavailable,
                    default => ProfessionalPublicStatusDecisionV1::Corrupted,
                },
            };
        } catch (Throwable) {
            return ProfessionalPublicStatusDecisionV1::DependencyUnavailable;
        }
    }
}
