<?php

namespace App\Application\ActiveGenerationReader\Contract;

use App\Application\ActiveGenerationReader\ActiveGenerationReadResult;

interface ActiveGenerationReader
{
    public function read(): ActiveGenerationReadResult;
}
