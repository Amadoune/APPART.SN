<?php

namespace Appart\Modules\RealEstateCatalog\Application\Promotion\Contract;

use Appart\Modules\RealEstateCatalog\Application\Promotion\PromotionCommandRecord;

interface PromotionCommandLedger
{
    public function find(string $commandId): ?PromotionCommandRecord;

    public function record(PromotionCommandRecord $record): void;
}
