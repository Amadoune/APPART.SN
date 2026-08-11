<?php

namespace App\Providers;

use App\Application\PublicationReviewProjectionActivation\PublicListingProjectionActivationAdapter;
use Appart\Modules\PublicationReview\Application\Projection\Contract\ProjectPublishedListingV1;
use Appart\Modules\PublicationReview\Application\Projection\Contract\PublicationReviewProjectionStore;
use Appart\Modules\PublicationReview\Application\Projection\Contract\PublicListingProjectionActivation;
use Appart\Modules\PublicationReview\Application\Projection\DeterministicProjectPublishedListing;
use Appart\Modules\PublicationReview\Application\Queue\Contract\ClaimPublicationReviewV1;
use Appart\Modules\PublicationReview\Application\Queue\Contract\PublicationReviewCommandLedger;
use Appart\Modules\PublicationReview\Application\Queue\Contract\PublicationReviewQueue;
use Appart\Modules\PublicationReview\Application\Queue\Contract\PublicationReviewQueueReaderV1;
use Appart\Modules\PublicationReview\Application\Queue\PublicationReviewConsumer;
use Appart\Modules\PublicationReview\Application\Review\Contract\ApprovePublicationV1;
use Appart\Modules\PublicationReview\Application\Review\Contract\BeginPublicationReviewV1;
use Appart\Modules\PublicationReview\Application\Review\Contract\PublicationReviewCommandStore;
use Appart\Modules\PublicationReview\Application\Review\DeterministicPublicationReviewCommands;
use Appart\Modules\PublicationReview\Infrastructure\Persistence\PostgreSql\PostgreSqlPublicationReviewQueue;
use Illuminate\Support\ServiceProvider;

final class PublicationReviewQueueServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PostgreSqlPublicationReviewQueue::class);
        $this->app->alias(PostgreSqlPublicationReviewQueue::class, PublicationReviewQueue::class);
        $this->app->alias(PostgreSqlPublicationReviewQueue::class, PublicationReviewQueueReaderV1::class);
        $this->app->alias(PostgreSqlPublicationReviewQueue::class, ClaimPublicationReviewV1::class);
        $this->app->alias(PostgreSqlPublicationReviewQueue::class, PublicationReviewCommandLedger::class);
        $this->app->alias(PostgreSqlPublicationReviewQueue::class, PublicationReviewCommandStore::class);
        $this->app->singleton(PublicationReviewConsumer::class);
        $this->app->singleton(DeterministicPublicationReviewCommands::class);
        $this->app->alias(DeterministicPublicationReviewCommands::class, BeginPublicationReviewV1::class);
        $this->app->alias(DeterministicPublicationReviewCommands::class, ApprovePublicationV1::class);
        $this->app->alias(PostgreSqlPublicationReviewQueue::class, PublicationReviewProjectionStore::class);
        $this->app->singleton(PublicListingProjectionActivationAdapter::class);
        $this->app->alias(PublicListingProjectionActivationAdapter::class, PublicListingProjectionActivation::class);
        $this->app->singleton(DeterministicProjectPublishedListing::class);
        $this->app->alias(DeterministicProjectPublishedListing::class, ProjectPublishedListingV1::class);
    }
}
