<?php

namespace App\Application\ProfessionalProfileRuntime\Contract;

use App\Application\ProfessionalProfileRuntime\ProfessionalProfileRuntimeStatus;

final readonly class ProfessionalProfileRuntimeReport
{
    public function __construct(
        public ProfessionalProfileRuntimeStatus $status,
        public ?string $componentCode = null,
        public string $policyVersion = 'professional-profile-runtime-v1',
    ) {}
}
