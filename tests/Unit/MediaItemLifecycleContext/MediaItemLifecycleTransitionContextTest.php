<?php

namespace Tests\Unit\MediaItemLifecycleContext;

use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaCollectionDecisionVersion;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaCollectionTransitionDecision;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleActorId;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleContextChecksum;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleContextVersion;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleExpectedVersion;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleOccurredAt;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleTransitionContext;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaPrimaryTransitionDisposition;
use Appart\Modules\Media\Domain\Exception\InvalidMediaValue;
use Appart\Modules\Media\Domain\ValueObject\MediaCollectionId;
use Appart\Modules\Media\Domain\ValueObject\MediaId;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class MediaItemLifecycleTransitionContextTest extends TestCase
{
    public function test_non_primary_decision_carries_no_replacement(): void
    {
        $decision = MediaCollectionTransitionDecision::notPrimary();
        self::assertSame(MediaPrimaryTransitionDisposition::NotPrimary, $decision->disposition);
        self::assertNull($decision->replacementMediaId);
    }

    public function test_primary_decision_carries_the_explicit_replacement(): void
    {
        $replacement = $this->media('a4601000-0000-4000-8000-000000000003');
        $decision = MediaCollectionTransitionDecision::replacementSelected($this->mediaId(), $replacement);
        self::assertSame(MediaPrimaryTransitionDisposition::ReplacementSelected, $decision->disposition);
        self::assertSame($replacement, $decision->replacementMediaId);
    }

    public function test_a_media_cannot_replace_itself(): void
    {
        $this->expectException(InvalidArgumentException::class);
        MediaCollectionTransitionDecision::replacementSelected($this->mediaId(), $this->mediaId());
    }

    public function test_context_is_complete_versioned_and_deterministic(): void
    {
        $first = $this->context();
        $second = $this->context();
        self::assertEquals($first, $second);
        self::assertSame(MediaItemLifecycleContextVersion::V1, $first->contractVersion);
        self::assertSame(7, $first->expectedVersion->value);
        self::assertSame(12, $first->collectionVersion->value);
        self::assertEquals($first->checksum(), $second->checksum());
        self::assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $first->checksum()->value);
    }

    public function test_every_context_field_changes_the_checksum(): void
    {
        $base = $this->context();
        $changed = new MediaItemLifecycleTransitionContext(
            MediaItemLifecycleContextVersion::V1,
            $base->collectionId,
            $base->mediaId,
            $base->expectedVersion,
            $base->collectionVersion,
            $base->actor,
            $base->occurredAt,
            MediaCollectionTransitionDecision::replacementSelected($base->mediaId, $this->media('a4601000-0000-4000-8000-000000000003')),
        );
        self::assertNotSame($base->checksum()->value, $changed->checksum()->value);
    }

    public function test_expected_version_must_be_positive(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new MediaItemLifecycleExpectedVersion(0);
    }

    public function test_collection_decision_version_cannot_be_negative(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new MediaCollectionDecisionVersion(-1);
    }

    public function test_actor_must_be_an_explicit_uuid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        MediaItemLifecycleActorId::fromString('actor');
    }

    public function test_occurred_at_must_be_explicit_utc(): void
    {
        $this->expectException(InvalidArgumentException::class);
        MediaItemLifecycleOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-23T10:00:00+02:00'));
    }

    public function test_checksum_type_rejects_non_sha256_values(): void
    {
        $this->expectException(InvalidArgumentException::class);
        MediaItemLifecycleContextChecksum::fromString('invalid');
    }

    /** @param class-string $class */
    #[DataProvider('immutableModels')]
    public function test_contract_models_are_final_and_readonly(string $class): void
    {
        $reflection = new ReflectionClass($class);
        self::assertTrue($reflection->isFinal());
        self::assertTrue($reflection->isReadOnly());
    }

    /** @return iterable<array{class-string}> */
    public static function immutableModels(): iterable
    {
        yield [MediaItemLifecycleTransitionContext::class];
        yield [MediaCollectionTransitionDecision::class];
        yield [MediaItemLifecycleExpectedVersion::class];
        yield [MediaCollectionDecisionVersion::class];
        yield [MediaItemLifecycleActorId::class];
        yield [MediaItemLifecycleOccurredAt::class];
        yield [MediaItemLifecycleContextChecksum::class];
    }

    private function context(): MediaItemLifecycleTransitionContext
    {
        return new MediaItemLifecycleTransitionContext(
            MediaItemLifecycleContextVersion::V1,
            MediaCollectionId::fromString('a4601000-0000-4000-8000-000000000001'),
            $this->mediaId(),
            new MediaItemLifecycleExpectedVersion(7),
            new MediaCollectionDecisionVersion(12),
            MediaItemLifecycleActorId::fromString('a4601000-0000-4000-8000-000000000004'),
            MediaItemLifecycleOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-23T10:00:00.123456Z')),
            MediaCollectionTransitionDecision::notPrimary(),
        );
    }

    private function mediaId(): MediaId
    {
        return $this->media('a4601000-0000-4000-8000-000000000002');
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
