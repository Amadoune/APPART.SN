<?php

namespace Tests\Unit\MediaItemLifecycleContext;

use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleAction;
use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleState;
use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleTransition;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaCollectionDecisionVersion;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaCollectionTransitionDecision;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleActorId;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleContextualAppend;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleContextualAppendInspection;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleContextualInspectionResult;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleContextualInspectionStatus;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleContextualWriteResult;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleContextVersion;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleExpectedVersion;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleOccurredAt;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleTransitionContext;
use Appart\Modules\Media\Application\MediaItemLifecyclePersistence\MediaItemLifecycleId;
use Appart\Modules\Media\Domain\Exception\InvalidMediaValue;
use Appart\Modules\Media\Domain\ValueObject\MediaCollectionId;
use Appart\Modules\Media\Domain\ValueObject\MediaId;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class MediaItemLifecycleContextualPersistenceContractTest extends TestCase
{
    public function test_write_results_are_closed(): void
    {
        self::assertSame(
            ['applied', 'already_applied', 'context_divergence', 'version_conflict', 'state_conflict', 'transition_rejected', 'corrupted'],
            array_column(MediaItemLifecycleContextualWriteResult::cases(), 'value'),
        );
    }

    public function test_append_requires_the_context_media_identity(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new MediaItemLifecycleContextualAppend(
            MediaItemLifecycleId::fromString('a4600000-0000-4000-8000-000000000041'),
            $this->transition(),
            $this->context('a4600000-0000-4000-8000-000000000042'),
        );
    }

    public function test_inspection_requires_the_exact_next_version(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new MediaItemLifecycleContextualAppendInspection(
            $this->id(),
            3,
            $this->transition(),
            $this->context($this->id()->value),
            $this->context($this->id()->value)->checksum(),
        );
    }

    public function test_found_result_requires_and_exposes_the_exact_snapshot(): void
    {
        $context = $this->context($this->id()->value);
        $snapshot = new MediaItemLifecycleContextualAppendInspection($this->id(), 2, $this->transition(), $context, $context->checksum());
        $result = MediaItemLifecycleContextualInspectionResult::found($snapshot);

        self::assertSame(MediaItemLifecycleContextualInspectionStatus::Found, $result->status);
        self::assertSame($snapshot, $result->snapshot);
    }

    public function test_missing_and_corrupted_results_carry_no_snapshot(): void
    {
        foreach ([MediaItemLifecycleContextualInspectionResult::missing($this->id()), MediaItemLifecycleContextualInspectionResult::corrupted($this->id())] as $result) {
            self::assertContains($result->status, [MediaItemLifecycleContextualInspectionStatus::Missing, MediaItemLifecycleContextualInspectionStatus::Corrupted]);
            self::assertNull($result->snapshot);
        }
    }

    private function id(): MediaItemLifecycleId
    {
        return MediaItemLifecycleId::fromString('a4600000-0000-4000-8000-000000000041');
    }

    private function transition(): MediaItemLifecycleTransition
    {
        return new MediaItemLifecycleTransition(MediaItemLifecycleState::Active, MediaItemLifecycleState::Removed, MediaItemLifecycleAction::Remove);
    }

    private function context(string $mediaId): MediaItemLifecycleTransitionContext
    {
        return new MediaItemLifecycleTransitionContext(
            MediaItemLifecycleContextVersion::V1,
            MediaCollectionId::fromString('a4601000-0000-4000-8000-000000000001'),
            $this->media($mediaId),
            new MediaItemLifecycleExpectedVersion(1),
            new MediaCollectionDecisionVersion(7),
            MediaItemLifecycleActorId::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'),
            MediaItemLifecycleOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-23T12:00:00.123456Z')),
            MediaCollectionTransitionDecision::notPrimary(),
        );
    }

    private function media(string $value): MediaId
    {
        try {
            return MediaId::fromString($value);
        } catch (InvalidMediaValue $error) {
            self::fail($error->getMessage());
        }
    }
}
