<?php

namespace Appart\Modules\MonetizationPayments\Domain\Model;

use Appart\Modules\MonetizationPayments\Domain\Event\PaymentAuthorized;
use Appart\Modules\MonetizationPayments\Domain\Event\PaymentCaptured;
use Appart\Modules\MonetizationPayments\Domain\Event\PaymentEvent;
use Appart\Modules\MonetizationPayments\Domain\Event\PaymentFailed;
use Appart\Modules\MonetizationPayments\Domain\Event\PaymentRefunded;
use Appart\Modules\MonetizationPayments\Domain\Exception\PaymentViolation;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\Money;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\OccurredAt;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\OrderId;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\PaymentId;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\PaymentMethod;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\PaymentProof;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\PaymentStatus;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\RefundProof;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\TransactionReference;

final class Payment
{
    /** @var list<PaymentAttempt> */
    private array $attempts = [];

    /** @var list<PaymentEvent> */
    private array $events = [];

    private int $version = 1;

    private function __construct(private readonly PaymentId $id, private readonly OrderId $orderId, private readonly Money $amount, private readonly PaymentMethod $method, private readonly TransactionReference $reference, private PaymentStatus $status, private OccurredAt $changedAt) {}

    public static function authorize(PaymentId $id, OrderId $orderId, Money $amount, PaymentMethod $method, TransactionReference $reference, OccurredAt $at): self
    {
        $self = new self($id, $orderId, $amount, $method, $reference, PaymentStatus::Authorized, $at);
        $self->attempts[] = new PaymentAttempt(1, PaymentStatus::Authorized, $at);
        $self->events[] = new PaymentAuthorized($id, $orderId, $amount, $method, $reference, 1, $at);

        return $self;
    }

    /** @param list<PaymentAttempt> $attempts */
    public static function reconstitute(PaymentId $id, OrderId $orderId, Money $amount, PaymentMethod $method, TransactionReference $reference, PaymentStatus $status, OccurredAt $changedAt, int $version, array $attempts): self
    {
        $last = $attempts === [] ? null : $attempts[array_key_last($attempts)];
        if ($version < 1 || count($attempts) !== $version || $last === null || $last->outcome !== $status || $last->occurredAt->value != $changedAt->value) {
            throw new PaymentViolation('Invalid persisted payment state.');
        }
        $previous = null;
        foreach ($attempts as $index => $attempt) {
            if ($attempt->sequence !== $index + 1 || ($previous !== null && $attempt->occurredAt->isBefore($previous))) {
                throw new PaymentViolation('Invalid persisted payment history.');
            }
            $previous = $attempt->occurredAt;
        }
        $outcomes = array_map(static fn (PaymentAttempt $attempt): PaymentStatus => $attempt->outcome, $attempts);
        $expected = match ($status) {
            PaymentStatus::Authorized => [PaymentStatus::Authorized],
            PaymentStatus::Captured => [PaymentStatus::Authorized, PaymentStatus::Captured],
            PaymentStatus::Failed => [PaymentStatus::Authorized, PaymentStatus::Failed],
            PaymentStatus::Refunded => [PaymentStatus::Authorized, PaymentStatus::Captured, PaymentStatus::Refunded],
        };
        if ($outcomes !== $expected) {
            throw new PaymentViolation('Invalid persisted payment transitions.');
        }
        $self = new self($id, $orderId, $amount, $method, $reference, $status, $changedAt);
        $self->version = $version;
        $self->attempts = $attempts;

        return $self;
    }

    public function capture(OccurredAt $at): void
    {
        $this->transition(PaymentStatus::Authorized, PaymentStatus::Captured, $at);
        $this->events[] = new PaymentCaptured($this->id, $this->orderId, $this->amount, $this->reference, $this->version, $at);
    }

    public function fail(OccurredAt $at): void
    {
        $this->transition(PaymentStatus::Authorized, PaymentStatus::Failed, $at);
        $this->events[] = new PaymentFailed($this->id, $this->orderId, $this->amount, $this->reference, $this->version, $at);
    }

    public function refund(OccurredAt $at): void
    {
        $this->transition(PaymentStatus::Captured, PaymentStatus::Refunded, $at);
        $this->events[] = new PaymentRefunded($this->id, $this->orderId, $this->amount, $this->reference, $this->version, $at);
    }

    private function transition(PaymentStatus $from, PaymentStatus $to, OccurredAt $at): void
    {
        if ($this->status !== $from || $at->isBefore($this->changedAt)) {
            throw new PaymentViolation("Invalid payment transition from {$this->status->value}.");
        }
        $this->status = $to;
        $this->changedAt = $at;
        $this->version++;
        $this->attempts[] = new PaymentAttempt(count($this->attempts) + 1, $to, $at);
    }

    public function id(): PaymentId
    {
        return $this->id;
    }

    public function orderId(): OrderId
    {
        return $this->orderId;
    }

    public function amount(): Money
    {
        return $this->amount;
    }

    public function method(): PaymentMethod
    {
        return $this->method;
    }

    public function reference(): TransactionReference
    {
        return $this->reference;
    }

    public function status(): PaymentStatus
    {
        return $this->status;
    }

    public function version(): int
    {
        return $this->version;
    }

    public function capturedProof(): PaymentProof
    {
        if ($this->status !== PaymentStatus::Captured) {
            throw new PaymentViolation('Only a captured payment can issue proof.');
        }

        return PaymentProof::fromCapturedPayment($this);
    }

    public function capturedAt(): OccurredAt
    {
        if ($this->status !== PaymentStatus::Captured) {
            throw new PaymentViolation('Payment is not captured.');
        }
        foreach (array_reverse($this->attempts) as $attempt) {
            if ($attempt->outcome === PaymentStatus::Captured) {
                return $attempt->occurredAt;
            }
        }
        throw new PaymentViolation('Captured payment history is incomplete.');
    }

    public function refundedProof(): RefundProof
    {
        if ($this->status !== PaymentStatus::Refunded) {
            throw new PaymentViolation('Only a refunded payment can issue refund proof.');
        }

        return RefundProof::fromRefundedPayment($this, $this->changedAt);
    }

    public function hasSameIntentAs(self $other): bool
    {
        return $this->orderId->value === $other->orderId->value
            && $this->amount->equals($other->amount)
            && $this->method === $other->method
            && $this->reference->value === $other->reference->value;
    }

    /** @return list<PaymentAttempt> */
    public function attempts(): array
    {
        return $this->attempts;
    }

    /** @return list<PaymentEvent> */
    public function releaseEvents(): array
    {
        $events = $this->events;
        $this->events = [];

        return $events;
    }
}
