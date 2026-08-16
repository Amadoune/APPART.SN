<?php

namespace App\Application\ActiveGenerationBootstrap\Contract;

use App\Application\ActiveGenerationBootstrap\BootstrapActiveProjectionGenerationCommand;
use App\Application\ActiveGenerationBootstrap\BootstrapActiveProjectionGenerationResult;

interface BootstrapActiveProjectionGenerationV1
{
    public function bootstrap(BootstrapActiveProjectionGenerationCommand $command): BootstrapActiveProjectionGenerationResult;
}
