<?php

namespace Appart\Modules\MonetizationPayments\Application\UseCase;

use Appart\Modules\MonetizationPayments\Application\Contract\PaymentRegistry;
use Appart\Modules\MonetizationPayments\Domain\Exception\AggregateNotFound;
use Appart\Modules\MonetizationPayments\Domain\Model\Payment;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\PaymentId;

abstract readonly class PaymentMutation
{
    public function __construct(protected PaymentRegistry $payments) {}

    /** @return array{Payment, int} */
    protected function load(PaymentId $id): array
    {
        $payment = $this->payments->find($id) ?? throw new AggregateNotFound('Payment not found.');

        return [$payment, $payment->version()];
    }
}
