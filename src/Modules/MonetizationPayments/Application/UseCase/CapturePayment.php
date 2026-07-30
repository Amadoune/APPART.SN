<?php

namespace Appart\Modules\MonetizationPayments\Application\UseCase;

use Appart\Modules\MonetizationPayments\Domain\Model\Payment;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\OccurredAt;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\PaymentId;

final readonly class CapturePayment extends PaymentMutation
{
    public function execute(PaymentId $id, OccurredAt $at): Payment
    {
        [$payment, $version] = $this->load($id);
        $payment->capture($at);
        $this->payments->save($payment, $version);

        return $payment;
    }
}
