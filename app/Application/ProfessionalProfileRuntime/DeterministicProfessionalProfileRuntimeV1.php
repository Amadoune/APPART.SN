<?php

namespace App\Application\ProfessionalProfileRuntime;

use App\Application\ProfessionalProfileRuntime\Contract\ProfessionalProfileRuntimeAvailabilityPolicy;
use App\Application\ProfessionalProfileRuntime\Contract\ProfessionalProfileRuntimeReport;
use App\Application\ProfessionalProfileRuntime\Contract\ProfessionalProfileRuntimeV1;
use Appart\Modules\Professionals\Application\ProfessionalProfilePersistence\Contract\ProfessionalPublicPortfolioStore;
use Appart\Modules\Professionals\Application\ProfessionalProfilePersistence\Contract\ProfessionalPublicProfileStore;
use Appart\Modules\Professionals\Application\ProfessionalProfilePersistence\Contract\ProfessionalVerificationStore;

final readonly class DeterministicProfessionalProfileRuntimeV1 implements ProfessionalProfileRuntimeV1
{
    public function __construct(
        private ProfessionalPublicProfileStore $publicProfile,
        private ProfessionalVerificationStore $verification,
        private ProfessionalPublicPortfolioStore $publicPortfolio,
        private ProfessionalProfileRuntimeAvailabilityPolicy $availability,
    ) {}

    public function publicProfile(): ProfessionalPublicProfileStore
    {
        return $this->publicProfile;
    }

    public function verification(): ProfessionalVerificationStore
    {
        return $this->verification;
    }

    public function publicPortfolio(): ProfessionalPublicPortfolioStore
    {
        return $this->publicPortfolio;
    }

    public function inspect(): ProfessionalProfileRuntimeReport
    {
        return $this->availability->inspect();
    }
}
