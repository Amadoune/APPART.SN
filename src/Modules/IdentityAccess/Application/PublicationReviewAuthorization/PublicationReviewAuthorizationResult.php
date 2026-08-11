<?php

namespace Appart\Modules\IdentityAccess\Application\PublicationReviewAuthorization;

final readonly class PublicationReviewAuthorizationResult
{
    public function __construct(public PublicationReviewAuthorizationStatus $status) {}
}
