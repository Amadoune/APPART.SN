<?php

namespace Appart\Modules\IdentityAccess\Application\AuthenticationAuthority;

use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;

final readonly class LoginIdentityResolution
{
    private function __construct(public LoginIdentityResolutionStatus $status, public ?AccountId $accountId) {}

    public static function resolved(AccountId $accountId): self
    {
        return new self(LoginIdentityResolutionStatus::Resolved, $accountId);
    }

    public static function notResolved(): self
    {
        return new self(LoginIdentityResolutionStatus::NotResolved, null);
    }

    public static function dependencyUnavailable(): self
    {
        return new self(LoginIdentityResolutionStatus::DependencyUnavailable, null);
    }
}
