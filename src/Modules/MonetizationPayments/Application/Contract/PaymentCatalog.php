<?php

namespace Appart\Modules\MonetizationPayments\Application\Contract;

use Appart\Modules\MonetizationPayments\Domain\ValueObject\PaymentId;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\PaymentProof;

interface PaymentCatalog
{
    public function capturedProof(PaymentId $id): ?PaymentProof;
}
