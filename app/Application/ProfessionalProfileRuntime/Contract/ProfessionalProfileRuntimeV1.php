<?php

namespace App\Application\ProfessionalProfileRuntime\Contract;

use Appart\Modules\Professionals\Application\ProfessionalProfilePersistence\Contract\ProfessionalPublicPortfolioStore;
use Appart\Modules\Professionals\Application\ProfessionalProfilePersistence\Contract\ProfessionalPublicProfileStore;
use Appart\Modules\Professionals\Application\ProfessionalProfilePersistence\Contract\ProfessionalVerificationStore;

interface ProfessionalProfileRuntimeV1
{
    public function publicProfile(): ProfessionalPublicProfileStore;

    public function verification(): ProfessionalVerificationStore;

    public function publicPortfolio(): ProfessionalPublicPortfolioStore;

    public function inspect(): ProfessionalProfileRuntimeReport;
}
