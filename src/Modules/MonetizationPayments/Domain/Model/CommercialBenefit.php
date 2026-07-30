<?php

namespace Appart\Modules\MonetizationPayments\Domain\Model;

use Appart\Modules\MonetizationPayments\Domain\Exception\PaymentViolation;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\BenefitPeriod;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\BenefitStatus;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\BenefitType;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\PaymentId;

final readonly class CommercialBenefit
{
    private function __construct(public BenefitType $type, public BenefitPeriod $period, public PaymentId $paymentId, public BenefitStatus $status) {}

    public static function grant(BenefitType $type, BenefitPeriod $period, PaymentId $paymentId): self
    {
        return new self($type, $period, $paymentId, BenefitStatus::Granted);
    }

    public static function reconstitute(BenefitType $type, BenefitPeriod $period, PaymentId $paymentId, BenefitStatus $status): self
    {
        return new self($type, $period, $paymentId, $status);
    }

    public function expire(): self
    {
        if ($this->status !== BenefitStatus::Granted) {
            throw new PaymentViolation('Only a granted benefit can expire.');
        }

        return new self($this->type, $this->period, $this->paymentId, BenefitStatus::Expired);
    }

    public function revoke(): self
    {
        if ($this->status !== BenefitStatus::Granted) {
            throw new PaymentViolation('Only a granted benefit can be revoked.');
        }

        return new self($this->type, $this->period, $this->paymentId, BenefitStatus::Revoked);
    }
}
