<?php

namespace Tests\Feature;

use App\Application\PublicationReviewProjectionActivation\PublicListingProjectionActivationAdapter;
use Appart\Modules\PublicationReview\Application\Projection\Contract\ProjectPublishedListingV1;
use Appart\Modules\PublicationReview\Application\Projection\Contract\PublicListingProjectionActivation;
use Appart\Modules\PublicationReview\Application\Projection\DeterministicProjectPublishedListing;
use Tests\TestCase;

final class PublicationReviewProjectionActivationCompositionTest extends TestCase
{
    public function test_projection_activation_contracts_resolve_to_certified_compositions(): void
    {
        self::assertInstanceOf(DeterministicProjectPublishedListing::class, $this->app->make(ProjectPublishedListingV1::class));
        self::assertInstanceOf(PublicListingProjectionActivationAdapter::class, $this->app->make(PublicListingProjectionActivation::class));
    }
}
