<?php

namespace App\Application\ActiveGenerationBootstrap\Contract;

use App\Application\ActiveGenerationBootstrap\ActiveGenerationBootstrapState;

interface ActiveGenerationBootstrapStateReader
{
    public function read(): ActiveGenerationBootstrapState;
}
