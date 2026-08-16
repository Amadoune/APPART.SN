<?php

namespace Tests\Feature;

use App\Application\IdentityAccessHttp\AuthenticatedSessionContext;
use App\Application\IdentityAccessHttp\Contract\IdentityAccessHttpRuntime;
use App\Application\IdentityAccessHttp\IdentityAccessHttpCommand;
use App\Application\IdentityAccessHttp\IdentityAccessHttpResult;
use App\Application\IdentityAccessHttp\IdentityAccessSessionInspection;
use App\Application\PublicationReviewExperience\Contract\PublicationReviewExperienceV1;
use App\Application\PublicationReviewExperience\DeterministicPublicationReviewExperience;
use App\Application\PublicationReviewExperience\PublicationReviewExperienceResult;
use App\Application\PublicationReviewExperience\PublicationReviewExperienceStatus;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use Appart\Modules\PublicationReview\Application\Queue\PublicationReviewQueueItem;
use Appart\Modules\PublicationReview\Application\Queue\PublicationReviewQueueItemState;
use DateTimeImmutable;
use Illuminate\Cookie\Middleware\EncryptCookies;
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

    public function test_claim_form_transports_a_stable_command_instant_for_replay(): void
    {
        $item = new PublicationReviewQueueItem(
            'event-1',
            'event-1',
            '123f9e41-1be9-419f-a92b-b247a04e8ee9',
            2,
            new DateTimeImmutable('2026-08-11T17:00:00+00:00'),
            PublicationReviewQueueItemState::Pending,
            1,
        );
        $html = view('publication-review', [
            'mode' => 'candidate',
            'result' => new PublicationReviewExperienceResult(PublicationReviewExperienceStatus::Available, ['item' => $item]),
        ])->render();

        self::assertMatchesRegularExpression('/name="occurredAt" value="\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{6}\+\d{2}:\d{2}"/', $html);
    }

    public function test_begin_review_form_transports_a_stable_command_instant_for_replay(): void
    {
        $item = new PublicationReviewQueueItem(
            'event-1',
            'event-1',
            '123f9e41-1be9-419f-a92b-b247a04e8ee9',
            2,
            new DateTimeImmutable('2026-08-11T17:00:00+00:00'),
            PublicationReviewQueueItemState::Claimed,
            2,
            'reviewer-account',
            new DateTimeImmutable('2026-08-11T17:05:00+00:00'),
        );
        $html = view('publication-review', [
            'mode' => 'claimed',
            'result' => new PublicationReviewExperienceResult(PublicationReviewExperienceStatus::Applied, ['item' => $item]),
        ])->render();

        self::assertMatchesRegularExpression('/name="occurredAt" value="\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{6}\+\d{2}:\d{2}"/', $html);
    }

    public function test_approve_form_transports_a_stable_command_instant_for_replay(): void
    {
        $html = view('publication-review', [
            'mode' => 'reviewing',
            'result' => new PublicationReviewExperienceResult(PublicationReviewExperienceStatus::Applied, ['version' => 3]),
            'queueItemId' => 'event-1',
            'listingId' => '123f9e41-1be9-419f-a92b-b247a04e8ee9',
            'submissionVersion' => 2,
        ])->render();

        self::assertMatchesRegularExpression('/name="occurredAt" value="\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{6}\+\d{2}:\d{2}"/', $html);
    }

    public function test_not_ready_view_is_not_reduced_to_publication_success(): void
    {
        $html = view('publication-review', [
            'mode' => 'not-ready',
            'result' => new PublicationReviewExperienceResult(PublicationReviewExperienceStatus::NotReady),
        ])->render();

        self::assertStringContainsString('L’annonce n’est pas encore prête.', $html);
        self::assertStringContainsString('Réessayez plus tard', $html);
        self::assertStringNotContainsString('Publication confirmée', $html);
        self::assertStringNotContainsString('L’annonce est maintenant publique.', $html);
    }

    public function test_applied_and_already_applied_keep_the_certified_success_view(): void
    {
        foreach ([PublicationReviewExperienceStatus::Applied, PublicationReviewExperienceStatus::AlreadyApplied] as $status) {
            $html = view('publication-review', [
                'mode' => 'confirmed',
                'result' => new PublicationReviewExperienceResult($status),
            ])->render();

            self::assertStringContainsString('Publication confirmée', $html);
            self::assertStringContainsString('L’annonce est maintenant publique.', $html);
            self::assertStringNotContainsString('L’annonce n’est pas encore prête.', $html);
        }
    }

    public function test_approve_http_adapter_keeps_not_ready_as_http_200_without_success_message(): void
    {
        config(['app.key' => 'base64:'.base64_encode(str_repeat('n', 32))]);
        $this->withoutMiddleware(EncryptCookies::class);
        $this->app->instance(IdentityAccessHttpRuntime::class, new PublicationReviewIdentityRuntime);
        $experience = $this->createMock(PublicationReviewExperienceV1::class);
        $experience->expects(self::once())
            ->method('approve')
            ->willReturn(new PublicationReviewExperienceResult(PublicationReviewExperienceStatus::NotReady));
        $this->app->instance(PublicationReviewExperienceV1::class, $experience);

        $this->withCredentials()
            ->withUnencryptedCookie('__Host-appart_session', 'review-session')
            ->post('/publication-review/item-1/approve', [
                'commandId' => '123f9e41-1be9-419f-a92b-b247a04e8ee1',
                'projectionCommandId' => '123f9e41-1be9-419f-a92b-b247a04e8ee2',
                'expectedVersion' => 3,
                'occurredAt' => '2026-08-16T12:00:00.000000+00:00',
                'listingId' => '123f9e41-1be9-419f-a92b-b247a04e8ee3',
                'submissionVersion' => 2,
            ])
            ->assertOk()
            ->assertSee('L’annonce n’est pas encore prête.')
            ->assertDontSee('Publication confirmée')
            ->assertDontSee('L’annonce est maintenant publique.');
    }

    public function test_approve_http_adapter_preserves_dependency_unavailable_mapping(): void
    {
        config(['app.key' => 'base64:'.base64_encode(str_repeat('n', 32))]);
        $this->withoutMiddleware(EncryptCookies::class);
        $this->app->instance(IdentityAccessHttpRuntime::class, new PublicationReviewIdentityRuntime);
        $experience = $this->createMock(PublicationReviewExperienceV1::class);
        $experience->expects(self::once())
            ->method('approve')
            ->willReturn(new PublicationReviewExperienceResult(PublicationReviewExperienceStatus::DependencyUnavailable));
        $this->app->instance(PublicationReviewExperienceV1::class, $experience);

        $this->withCredentials()
            ->withUnencryptedCookie('__Host-appart_session', 'review-session')
            ->post('/publication-review/item-1/approve', [
                'commandId' => '123f9e41-1be9-419f-a92b-b247a04e8ee1',
                'projectionCommandId' => '123f9e41-1be9-419f-a92b-b247a04e8ee2',
                'expectedVersion' => 3,
                'occurredAt' => '2026-08-16T12:00:00.000000+00:00',
                'listingId' => '123f9e41-1be9-419f-a92b-b247a04e8ee3',
                'submissionVersion' => 2,
            ])
            ->assertServiceUnavailable();
    }
}

final class PublicationReviewIdentityRuntime implements IdentityAccessHttpRuntime
{
    public function execute(IdentityAccessHttpCommand $command): IdentityAccessHttpResult
    {
        throw new \LogicException('Not used.');
    }

    public function inspectSession(string $secret, DateTimeImmutable $at): IdentityAccessSessionInspection
    {
        return $secret === 'review-session'
            ? IdentityAccessSessionInspection::valid(new AuthenticatedSessionContext(
                AccountId::fromString('123f9e41-1be9-419f-a92b-b247a04e8ee4'),
                '123f9e41-1be9-419f-a92b-b247a04e8ee5',
            ))
            : IdentityAccessSessionInspection::invalid();
    }
}
