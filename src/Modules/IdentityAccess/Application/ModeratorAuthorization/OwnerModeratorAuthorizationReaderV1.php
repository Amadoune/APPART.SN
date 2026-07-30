<?php

namespace Appart\Modules\IdentityAccess\Application\ModeratorAuthorization;

use Appart\Modules\IdentityAccess\Application\Contract\AccountRegistry;
use Appart\Modules\IdentityAccess\Application\ModeratorAuthorization\Contract\ModeratorAuthorizationReaderV1;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use Appart\Modules\IdentityAccess\Domain\ValueObject\RoleId;
use DateTimeImmutable;
use Throwable;

final readonly class OwnerModeratorAuthorizationReaderV1 implements ModeratorAuthorizationReaderV1
{
    public function __construct(private AccountRegistry $accounts) {}

    public function authorize(
        AccountId $accountId,
        ModerationCapabilityV1 $capability,
        DateTimeImmutable $observedAt,
    ): ModeratorAuthorizationDecisionV1 {
        try {
            $account = $this->accounts->find($accountId);
        } catch (Throwable) {
            return ModeratorAuthorizationDecisionV1::DependencyUnavailable;
        }

        if ($account === null) {
            return ModeratorAuthorizationDecisionV1::Denied;
        }
        if (! $account->id()->equals($accountId)) {
            return ModeratorAuthorizationDecisionV1::Corrupted;
        }

        $requiredRole = match ($capability) {
            ModerationCapabilityV1::Report,
            ModerationCapabilityV1::Validate,
            ModerationCapabilityV1::Investigate,
            ModerationCapabilityV1::Decide => RoleId::fromString('moderator'),
            ModerationCapabilityV1::Audit => RoleId::fromString('moderation_auditor'),
        };

        return $account->hasRole($requiredRole)
            ? ModeratorAuthorizationDecisionV1::Allowed
            : ModeratorAuthorizationDecisionV1::Denied;
    }
}
