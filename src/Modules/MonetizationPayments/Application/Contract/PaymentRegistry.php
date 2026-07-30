<?php

namespace Appart\Modules\MonetizationPayments\Application\Contract;

use Appart\Modules\MonetizationPayments\Domain\Model\Payment;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\PaymentId;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\PaymentRegistration;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\TransactionReference;

interface PaymentRegistry
{
    /** Returns a detached aggregate reconstructed without previously published events. */
    public function find(PaymentId $id): ?Payment;

    /** Atomically reserves the reference: same intent is an idempotent replay, different intent is a conflict. */
    public function registerIdempotently(Payment $payment, TransactionReference $reference): PaymentRegistration;

    public function save(Payment $payment, int $expectedVersion): void;
}
/** Saves a clean snapshot only when the stored version equals expectedVersion. */
