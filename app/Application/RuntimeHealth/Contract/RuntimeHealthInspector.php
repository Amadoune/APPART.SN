<?php

namespace App\Application\RuntimeHealth\Contract;

use App\Application\RuntimeHealth\RuntimeHealthResult;

interface RuntimeHealthInspector
{
    public function inspect(): RuntimeHealthResult;
}
