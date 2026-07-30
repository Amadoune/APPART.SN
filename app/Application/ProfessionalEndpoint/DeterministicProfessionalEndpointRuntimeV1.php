<?php

namespace App\Application\ProfessionalEndpoint;

use App\Application\ProfessionalEndpoint\Contract\ProfessionalEndpointRuntimeV1;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use Appart\Modules\Professionals\Application\ProfessionalMandateResolution\Contract\ProfessionalMandateResolverV1;
use Appart\Modules\Professionals\Application\ProfessionalMandateResolution\ProfessionalMandateResolutionStatusV1;
use Appart\Modules\Professionals\Application\ProfessionalMandateResolution\ProfessionalMandateResolutionV1;
use Appart\Modules\Professionals\Application\ProfessionalStatusPublicRead\Contract\ProfessionalPublicStatusReaderV1;
use Appart\Modules\Professionals\Application\ProfessionalStatusPublicRead\ProfessionalPublicStatusDecisionV1;

final readonly class DeterministicProfessionalEndpointRuntimeV1 implements ProfessionalEndpointRuntimeV1
{
    public function __construct(
        private ProfessionalMandateResolverV1 $mandates,
        private ProfessionalPublicStatusReaderV1 $statuses,
    ) {}

    public function mandate(AccountId $accountId): ProfessionalMandateResolutionV1
    {
        return $this->mandates->resolve($accountId->value);
    }

    public function status(AccountId $accountId): ProfessionalPublicStatusDecisionV1
    {
        $mandate = $this->mandate($accountId);

        return match ($mandate->status) {
            ProfessionalMandateResolutionStatusV1::Resolved => $this->statuses->read($mandate->professionalId),
            ProfessionalMandateResolutionStatusV1::NotMandated => ProfessionalPublicStatusDecisionV1::Missing,
            ProfessionalMandateResolutionStatusV1::Ambiguous,
            ProfessionalMandateResolutionStatusV1::Corrupted => ProfessionalPublicStatusDecisionV1::Corrupted,
            ProfessionalMandateResolutionStatusV1::DependencyUnavailable => ProfessionalPublicStatusDecisionV1::DependencyUnavailable,
        };
    }
}
