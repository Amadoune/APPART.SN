<?php

namespace Tests\Feature;

use Appart\Modules\PublicationReview\Application\Review\Contract\ApprovePublicationV1;
use Appart\Modules\PublicationReview\Application\Review\Contract\BeginPublicationReviewV1;
use Appart\Modules\PublicationReview\Application\Review\DeterministicPublicationReviewCommands;
use Tests\TestCase;

final class PublicationReviewCommandsCompositionTest extends TestCase
{
    public function test_review_commands_resolve_to_the_same_lazy_singleton(): void
    {
        $begin = $this->app->make(BeginPublicationReviewV1::class);
        $approve = $this->app->make(ApprovePublicationV1::class);

        self::assertInstanceOf(DeterministicPublicationReviewCommands::class, $begin);
        self::assertSame($begin, $approve);
    }
}
