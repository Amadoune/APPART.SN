<?php

namespace Tests\Unit\PublicationReviewExperience;

use App\Application\PublicationReviewExperience\DeterministicPublicationReviewExperience;
use App\Application\PublicationReviewExperience\PublicationReviewExperienceStatus;
use Appart\Modules\IdentityAccess\Application\PublicationReviewAuthorization\Contract\PublicationReviewAuthorizationReaderV1;
use Appart\Modules\IdentityAccess\Application\PublicationReviewAuthorization\PublicationReviewAuthorizationResult;
use Appart\Modules\IdentityAccess\Application\PublicationReviewAuthorization\PublicationReviewAuthorizationStatus;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use Appart\Modules\PublicationReview\Application\Projection\Contract\ProjectPublishedListingV1;
use Appart\Modules\PublicationReview\Application\Projection\ProjectPublishedListingResult;
use Appart\Modules\PublicationReview\Application\Projection\ProjectPublishedListingStatus;
use Appart\Modules\PublicationReview\Application\Queue\Contract\ClaimPublicationReviewV1;
use Appart\Modules\PublicationReview\Application\Queue\Contract\PublicationReviewQueueReaderV1;
use Appart\Modules\PublicationReview\Application\Queue\PublicationReviewQueuePage;
use Appart\Modules\PublicationReview\Application\Queue\PublicationReviewQueueReadStatus;
use Appart\Modules\PublicationReview\Application\Review\Contract\ApprovePublicationV1;
use Appart\Modules\PublicationReview\Application\Review\Contract\BeginPublicationReviewV1;
use Appart\Modules\PublicationReview\Application\Review\PublicationReviewCommandResult;
use Appart\Modules\PublicationReview\Application\Review\PublicationReviewCommandStatus;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class DeterministicPublicationReviewExperienceTest extends TestCase
{
    public function test_denied_queue_access_is_fail_closed_before_reading_queue(): void
    {
        $authorization = $this->createMock(PublicationReviewAuthorizationReaderV1::class);
        $authorization->expects(self::once())->method('authorize')->willReturn(new PublicationReviewAuthorizationResult(PublicationReviewAuthorizationStatus::Denied));
        $queue = $this->createMock(PublicationReviewQueueReaderV1::class);
        $queue->expects(self::never())->method('read');

        $result = $this->service($authorization, $queue)->queue($this->account(), $this->at());

        self::assertSame(PublicationReviewExperienceStatus::Forbidden, $result->status);
    }

    public function test_approval_projects_only_after_a_successful_certified_command(): void
    {
        $authorization = $this->createMock(PublicationReviewAuthorizationReaderV1::class);
        $authorization->method('authorize')->willReturn(new PublicationReviewAuthorizationResult(PublicationReviewAuthorizationStatus::Allowed));
        $approve = $this->createMock(ApprovePublicationV1::class);
        $approve->expects(self::once())->method('approve')->with('queue-1', 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa', 3, $this->account()->value, $this->at())->willReturn(new PublicationReviewCommandResult(PublicationReviewCommandStatus::Applied, 4));
        $project = $this->createMock(ProjectPublishedListingV1::class);
        $project->expects(self::once())->method('project')->with('11111111-1111-4111-8111-111111111111', 9, 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb', $this->at())->willReturn(new ProjectPublishedListingResult(ProjectPublishedListingStatus::Applied, 5));

        $result = $this->service($authorization, approve: $approve, project: $project)->approve(
            $this->account(), 'queue-1', '11111111-1111-4111-8111-111111111111', 7,
            'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa', 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb', 3, $this->at(),
        );

        self::assertSame(PublicationReviewExperienceStatus::Applied, $result->status);
    }

    private function service(PublicationReviewAuthorizationReaderV1 $authorization, ?PublicationReviewQueueReaderV1 $queue = null, ?ApprovePublicationV1 $approve = null, ?ProjectPublishedListingV1 $project = null): DeterministicPublicationReviewExperience
    {
        if ($queue === null) {
            $queue = $this->createStub(PublicationReviewQueueReaderV1::class);
            $queue->method('read')->willReturn(new PublicationReviewQueuePage(PublicationReviewQueueReadStatus::Empty));
        }

        return new DeterministicPublicationReviewExperience(
            $authorization,
            $queue,
            $this->createStub(ClaimPublicationReviewV1::class),
            $this->createStub(BeginPublicationReviewV1::class),
            $approve ?? $this->createStub(ApprovePublicationV1::class),
            $project ?? $this->createStub(ProjectPublishedListingV1::class),
        );
    }

    private function account(): AccountId
    {
        return AccountId::fromString('99999999-9999-4999-8999-999999999999');
    }

    private function at(): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-08-11T14:00:00+00:00');
    }
}
