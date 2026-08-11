<?php

namespace Appart\Modules\IdentityAccess\Application\PublicationReviewAuthorization;

use Appart\Modules\IdentityAccess\Application\Contract\AccountRegistry;
use Appart\Modules\IdentityAccess\Application\PublicationReviewAuthorization\Contract\PublicationReviewAuthorizationReaderV1;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use Appart\Modules\IdentityAccess\Domain\ValueObject\RoleId;
use DateTimeImmutable;
use Throwable;

final readonly class OwnerPublicationReviewAuthorizationReaderV1 implements PublicationReviewAuthorizationReaderV1
{
    public function __construct(private AccountRegistry $accounts) {}

    public function authorize(AccountId $accountId, PublicationReviewCapabilityV1 $capability, DateTimeImmutable $observedAt): PublicationReviewAuthorizationResult
    {
        try {
            $account = $this->accounts->find($accountId);
        } catch (Throwable) {
            return new PublicationReviewAuthorizationResult(PublicationReviewAuthorizationStatus::DependencyUnavailable);
        }

        if ($account === null) {
            return new PublicationReviewAuthorizationResult(PublicationReviewAuthorizationStatus::Denied);
        }
        if (! $account->id()->equals($accountId)) {
            return new PublicationReviewAuthorizationResult(PublicationReviewAuthorizationStatus::DependencyUnavailable);
        }

        $requiredRole = match ($capability) {
            PublicationReviewCapabilityV1::ReadPublicationReviewQueue,
            PublicationReviewCapabilityV1::ClaimPublicationReview,
            PublicationReviewCapabilityV1::BeginPublicationReview,
            PublicationReviewCapabilityV1::ApprovePublication => RoleId::fromString('publication_reviewer'),
        };

        return new PublicationReviewAuthorizationResult(
            $account->hasRole($requiredRole)
                ? PublicationReviewAuthorizationStatus::Allowed
                : PublicationReviewAuthorizationStatus::Denied,
        );
    }
}
