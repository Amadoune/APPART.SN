<?php

namespace Appart\Modules\ListingLifecycle\Application\ContactPrincipalBoundary;

use LogicException;

final readonly class ListingContactPrincipalResultV1
{
    /**
     * @param  non-empty-string|null  $accountId  Opaque canonical AccountId.
     */
    private function __construct(
        public ListingContactPrincipalStatusV1 $status,
        public ?string $accountId = null,
    ) {
        if (($status === ListingContactPrincipalStatusV1::Resolved) !== ($accountId !== null)) {
            throw new LogicException('Only a resolved contact principal may expose an AccountId.');
        }
    }

    /**
     * @param  non-empty-string  $accountId  Opaque canonical AccountId.
     */
    public static function resolved(string $accountId): self
    {
        return new self(ListingContactPrincipalStatusV1::Resolved, $accountId);
    }

    public static function missing(): self
    {
        return new self(ListingContactPrincipalStatusV1::Missing);
    }

    public static function notAssigned(): self
    {
        return new self(ListingContactPrincipalStatusV1::NotAssigned);
    }

    public static function ambiguous(): self
    {
        return new self(ListingContactPrincipalStatusV1::Ambiguous);
    }

    public static function corrupted(): self
    {
        return new self(ListingContactPrincipalStatusV1::Corrupted);
    }

    public static function dependencyUnavailable(): self
    {
        return new self(ListingContactPrincipalStatusV1::DependencyUnavailable);
    }
}
