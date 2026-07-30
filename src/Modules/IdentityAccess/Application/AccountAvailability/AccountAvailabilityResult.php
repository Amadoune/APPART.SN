<?php

namespace Appart\Modules\IdentityAccess\Application\AccountAvailability;

use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusState;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use DateTimeImmutable;

final readonly class AccountAvailabilityResult
{
    public const string POLICY_VERSION = 'account-availability-v1';

    public function __construct(
        public AccountId $accountId,
        public AccountAvailabilityPurpose $purpose,
        public AccountAvailabilityStatus $status,
        public DateTimeImmutable $observedAt,
        public ?AccountStatusState $accountStatus,
        public ?int $accountStatusVersion,
        public ?AccountClosureState $closureState,
        public ?int $closureVersion,
        public string $policyVersion = self::POLICY_VERSION,
    ) {}

    public function isAvailable(): bool
    {
        return $this->status === AccountAvailabilityStatus::Available;
    }
}
