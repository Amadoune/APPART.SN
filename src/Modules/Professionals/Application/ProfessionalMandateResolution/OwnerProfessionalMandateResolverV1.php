<?php

namespace Appart\Modules\Professionals\Application\ProfessionalMandateResolution;

use Appart\Modules\Professionals\Application\ProfessionalMandateOwnerSource\Contract\ProfessionalMandateOwnerSource;
use Appart\Modules\Professionals\Application\ProfessionalMandateOwnerSource\ProfessionalMandateOwnerSourceStatus;
use Appart\Modules\Professionals\Application\ProfessionalMandateResolution\Contract\ProfessionalMandateResolverV1;

final readonly class OwnerProfessionalMandateResolverV1 implements ProfessionalMandateResolverV1
{
    public function __construct(private ProfessionalMandateOwnerSource $source) {}

    public function resolve(string $accountId): ProfessionalMandateResolutionV1
    {
        $result = $this->source->resolve($accountId);

        return match ($result->status) {
            ProfessionalMandateOwnerSourceStatus::Resolved => ProfessionalMandateResolutionV1::resolved($result->professionalId),
            ProfessionalMandateOwnerSourceStatus::NotMandated => ProfessionalMandateResolutionV1::notMandated(),
            ProfessionalMandateOwnerSourceStatus::Ambiguous => ProfessionalMandateResolutionV1::ambiguous(),
            ProfessionalMandateOwnerSourceStatus::Corrupted => ProfessionalMandateResolutionV1::corrupted(),
            ProfessionalMandateOwnerSourceStatus::DependencyUnavailable => ProfessionalMandateResolutionV1::dependencyUnavailable(),
        };
    }
}
