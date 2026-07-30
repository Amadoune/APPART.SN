<?php

namespace App\Application\MediaIngestionRuntime\Contract;

interface MediaIngestionRuntimeAvailabilityPolicy
{
    public function inspect(): MediaIngestionRuntimeReport;
}
