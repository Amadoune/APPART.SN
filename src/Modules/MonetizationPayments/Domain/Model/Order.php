<?php

namespace Appart\Modules\MonetizationPayments\Domain\Model;

use Appart\Modules\MonetizationPayments\Domain\Event\BenefitExpired;
use Appart\Modules\MonetizationPayments\Domain\Event\BenefitGranted;
use Appart\Modules\MonetizationPayments\Domain\Event\BenefitRevoked;
use Appart\Modules\MonetizationPayments\Domain\Event\OrderCreated;
use Appart\Modules\MonetizationPayments\Domain\Event\PaymentEvent;
use Appart\Modules\MonetizationPayments\Domain\Exception\PaymentViolation;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\BenefitPeriod;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\BenefitStatus;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\Money;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\OccurredAt;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\OrderId;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\OrderStatus;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\PaymentProof;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\RefundProof;

final class Order
{
    /** @var list<OrderLine> */
    private array $lines;

    /** @var list<CommercialBenefit> */
    private array $benefits = [];

    /** @var list<PaymentEvent> */
    private array $events = [];

    private int $version = 1;

    /** @var list<OrderHistoryEntry> */
    private array $history = [];

    /** @param list<OrderLine> $lines */
    private function __construct(private readonly OrderId $id, array $lines, private readonly Money $total, private OrderStatus $status, private OccurredAt $changedAt)
    {
        $this->lines = $lines;
    }

    /** @param list<OrderLine> $lines */
    public static function create(OrderId $id, array $lines, OccurredAt $at): self
    {
        if ($lines === []) {
            throw new PaymentViolation('An order requires at least one line.');
        }
        $seen = [];
        foreach ($lines as $line) {
            if (isset($seen[$line->productId->value])) {
                throw new PaymentViolation('One line represents one unique product; quantities are not supported.');
            }
            $seen[$line->productId->value] = true;
        }
        $total = $lines[0]->unitPrice;
        foreach (array_slice($lines, 1) as $line) {
            $total = $total->add($line->unitPrice);
        }
        $self = new self($id, $lines, $total, OrderStatus::PendingPayment, $at);
        $self->history[] = new OrderHistoryEntry(OrderStatus::PendingPayment, $at, 1);
        $self->events[] = new OrderCreated($id, $lines, $total, 1, $at);

        return $self;
    }

    /**
     * @param  list<OrderLine>  $lines
     * @param  list<CommercialBenefit>  $benefits
     * @param  list<OrderHistoryEntry>  $history
     */
    public static function reconstitute(OrderId $id, array $lines, Money $total, OrderStatus $status, OccurredAt $changedAt, int $version, array $benefits, array $history): self
    {
        $last = $history === [] ? null : $history[array_key_last($history)];
        if ($version < 1 || $lines === [] || $last === null || $last->version !== $version || $last->status !== $status || $last->occurredAt->value != $changedAt->value) {
            throw new PaymentViolation('Invalid persisted order state.');
        }
        $calculated = $lines[0]->unitPrice;
        $seen = [$lines[0]->productId->value => true];
        foreach (array_slice($lines, 1) as $line) {
            if (isset($seen[$line->productId->value])) {
                throw new PaymentViolation('Invalid persisted order state.');
            }
            $seen[$line->productId->value] = true;
            $calculated = $calculated->add($line->unitPrice);
        }
        if (! $calculated->equals($total)) {
            throw new PaymentViolation('Invalid persisted order total.');
        }
        $previous = null;
        foreach ($history as $index => $entry) {
            if ($entry->version !== $index + 1 || ($previous !== null && $entry->occurredAt->isBefore($previous))) {
                throw new PaymentViolation('Invalid persisted order chronology.');
            }
            $previous = $entry->occurredAt;
        }
        $expectedStatuses = match ($status) {
            OrderStatus::PendingPayment => [OrderStatus::PendingPayment],
            OrderStatus::Paid => [OrderStatus::PendingPayment, OrderStatus::Paid],
            OrderStatus::BenefitExpired => [OrderStatus::PendingPayment, OrderStatus::Paid, OrderStatus::BenefitExpired],
            OrderStatus::Refunded => count($benefits) === 0
                ? [OrderStatus::PendingPayment, OrderStatus::Refunded]
                : [OrderStatus::PendingPayment, OrderStatus::Paid, OrderStatus::Refunded],
        };
        if (array_map(static fn (OrderHistoryEntry $entry): OrderStatus => $entry->status, $history) !== $expectedStatuses) {
            throw new PaymentViolation('Invalid persisted order transitions.');
        }
        foreach ($benefits as $benefit) {
            $expectedBenefitStatus = match ($status) {
                OrderStatus::Paid => BenefitStatus::Granted,
                OrderStatus::BenefitExpired => BenefitStatus::Expired,
                OrderStatus::Refunded => BenefitStatus::Revoked,
                OrderStatus::PendingPayment => null,
            };
            if ($expectedBenefitStatus === null || $benefit->status !== $expectedBenefitStatus) {
                throw new PaymentViolation('Invalid persisted benefit state.');
            }
        }
        if (($status === OrderStatus::PendingPayment && $benefits !== []) || (($status === OrderStatus::Paid || $status === OrderStatus::BenefitExpired) && count($benefits) !== count($lines)) || ($status === OrderStatus::Refunded && $benefits !== [] && count($benefits) !== count($lines))) {
            throw new PaymentViolation('Invalid persisted benefits.');
        }
        $self = new self($id, $lines, $total, $status, $changedAt);
        $self->version = $version;
        $self->benefits = $benefits;
        $self->history = $history;

        return $self;
    }

    public function grantBenefit(PaymentProof $proof, OccurredAt $at): void
    {
        $this->assertChronology($at);
        if ($this->status !== OrderStatus::PendingPayment || $proof->orderId->value !== $this->id->value || ! $proof->amount->equals($this->total)) {
            throw new PaymentViolation('A matching captured payment is required.');
        }
        foreach ($this->lines as $line) {
            $period = new BenefitPeriod($at->value, $at->value->modify("+{$line->benefitDays} days"));
            $this->benefits[] = CommercialBenefit::grant($line->benefitType, $period, $proof->paymentId);
            $this->events[] = new BenefitGranted($this->id, $proof->paymentId, $proof->reference, $line->benefitType, $period, $this->version + 1, $at);
        }
        $this->status = OrderStatus::Paid;
        $this->changedAt = $at;
        $this->version++;
        $this->history[] = new OrderHistoryEntry(OrderStatus::Paid, $at, $this->version);
    }

    public function expireBenefit(OccurredAt $at): void
    {
        $this->assertChronology($at);
        if ($this->status !== OrderStatus::Paid || $this->benefits === []) {
            throw new PaymentViolation('Benefit cannot expire.');
        }
        foreach ($this->benefits as $index => $benefit) {
            if ($at->value < $benefit->period->endsAt) {
                throw new PaymentViolation('Benefit cannot expire.');
            }
        }
        foreach ($this->benefits as $index => $benefit) {
            $this->benefits[$index] = $benefit->expire();
            $this->events[] = new BenefitExpired($this->id, $benefit->paymentId, $benefit->type, $benefit->period, $this->version + 1, $at);
        }
        $this->status = OrderStatus::BenefitExpired;
        $this->changedAt = $at;
        $this->version++;
        $this->history[] = new OrderHistoryEntry(OrderStatus::BenefitExpired, $at, $this->version);
    }

    public function revokeBenefitsAfterRefund(RefundProof $proof, OccurredAt $at): void
    {
        $this->assertChronology($at);
        if (($this->status !== OrderStatus::Paid || $this->benefits === []) && ($this->status !== OrderStatus::PendingPayment || $this->benefits !== [])) {
            throw new PaymentViolation('Refund has no active commercial benefit to revoke.');
        }
        foreach ($this->benefits as $benefit) {
            if ($proof->orderId->value !== $this->id->value || $benefit->paymentId->value !== $proof->paymentId->value || $benefit->status !== BenefitStatus::Granted) {
                throw new PaymentViolation('Refund proof does not match active benefits.');
            }
        }
        foreach ($this->benefits as $index => $benefit) {
            $this->benefits[$index] = $benefit->revoke();
            $this->events[] = new BenefitRevoked($this->id, $proof->paymentId, $proof->reference, $benefit->type, $benefit->period, $this->version + 1, $at);
        }
        $this->status = OrderStatus::Refunded;
        $this->changedAt = $at;
        $this->version++;
        $this->history[] = new OrderHistoryEntry(OrderStatus::Refunded, $at, $this->version);
    }

    private function assertChronology(OccurredAt $at): void
    {
        if ($at->isBefore($this->changedAt)) {
            throw new PaymentViolation('Backdated order transition.');
        }
    }

    public function id(): OrderId
    {
        return $this->id;
    }

    public function total(): Money
    {
        return $this->total;
    }

    public function status(): OrderStatus
    {
        return $this->status;
    }

    /** @return list<OrderLine> */
    public function lines(): array
    {
        return $this->lines;
    }

    /** @return list<CommercialBenefit> */
    public function benefits(): array
    {
        return $this->benefits;
    }

    public function version(): int
    {
        return $this->version;
    }

    /** @return list<OrderHistoryEntry> */
    public function history(): array
    {
        return $this->history;
    }

    /** @return list<PaymentEvent> */
    public function releaseEvents(): array
    {
        $events = $this->events;
        $this->events = [];

        return $events;
    }
}
