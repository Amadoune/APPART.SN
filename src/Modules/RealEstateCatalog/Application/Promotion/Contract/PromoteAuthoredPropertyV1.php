<?php

namespace Appart\Modules\RealEstateCatalog\Application\Promotion\Contract;

use Appart\Modules\RealEstateCatalog\Application\Promotion\PromoteAuthoredPropertyCommand;
use Appart\Modules\RealEstateCatalog\Application\Promotion\PromoteAuthoredPropertyResult;

interface PromoteAuthoredPropertyV1
{
    public function promote(PromoteAuthoredPropertyCommand $command): PromoteAuthoredPropertyResult;
}
