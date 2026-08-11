<?php

namespace Tests\Feature;

use App\Application\PublicationReviewExperience\Contract\PublicationReviewExperienceV1;
use App\Application\PublicationReviewExperience\DeterministicPublicationReviewExperience;
use App\Application\PublicationReviewExperience\PublicationReviewExperienceResult;
use App\Application\PublicationReviewExperience\PublicationReviewExperienceStatus;
use Tests\TestCase;

final class PublicationReviewExperienceTest extends TestCase
{
    public function test_routes_are_protected_and_composition_is_real(): void
    {
        self::assertInstanceOf(DeterministicPublicationReviewExperience::class, $this->app->make(PublicationReviewExperienceV1::class));
        $this->get('/publication-review')->assertStatus(401)->assertJson(['status' => 'authentication_required']);
        self::assertSame('https://appart.test/publication-review', route('publication-review.index'));
    }

    public function test_empty_queue_view_is_accessible_and_contains_no_report_moderation_surface(): void
    {
        $html = view('publication-review', [
            'mode' => 'queue',
            'result' => new PublicationReviewExperienceResult(PublicationReviewExperienceStatus::Empty),
        ])->render();

        self::assertStringContainsString('<h1>Publier avec attention.</h1>', $html);
        self::assertStringContainsString('Aucune candidature en attente.', $html);
        self::assertStringNotContainsString('signalement', mb_strtolower($html));
    }
}
