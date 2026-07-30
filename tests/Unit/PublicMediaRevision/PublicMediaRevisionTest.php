<?php

namespace Tests\Unit\PublicMediaRevision;

use App\Application\PublicMediaRevision\PublicMediaRevisionPolicy;
use App\Application\PublicMediaRevision\PublicMediaRevisionPromotion;
use App\Application\PublicMediaRevision\PublicMediaRevisionRelation;
use App\Application\PublicMediaRevision\PublicMediaRevisionStrategy;
use App\Application\PublicMediaRevision\PublicMediaRevisionVersion;
use App\Application\PublicProjectionStore\PublicProjectionPromotionReadiness;
use App\Application\PublicProjectionStore\PublicProjectionWatermark;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PublicMediaRevisionTest extends TestCase
{
    public function test_same_explicit_inputs_always_produce_the_same_stable_revision(): void
    {
        $strategy = new PublicMediaRevisionStrategy;
        $payload = '{"cover":"media:1","gallery":["media:1","media:2"]}';
        $first = $strategy->revise(7, $payload, 'media-collection:7:ordered');
        $second = $strategy->revise(7, $payload, 'media-collection:7:ordered');

        self::assertTrue($first->sameFactAs($second));
        self::assertSame(7, $first->watermarkVersion());
    }

    public function test_public_payload_change_is_explicit_and_reproducible(): void
    {
        $strategy = new PublicMediaRevisionStrategy;
        $previous = $strategy->revise(7, '{"cover":"media:1"}', 'media-collection:7:cover-selected');
        $changed = $strategy->revise(8, '{"cover":"media:2"}', 'media-collection:8:cover-selected');

        self::assertNotSame($previous->checksum->value, $changed->checksum->value);
        self::assertSame($changed->checksum->value, $strategy->revise(8, '{"cover":"media:2"}', 'media-collection:8:cover-selected')->checksum->value);
    }

    public function test_promotion_decisions_are_exhaustive_and_deterministic(): void
    {
        $strategy = new PublicMediaRevisionStrategy;
        $policy = new PublicMediaRevisionPolicy;
        $stable = $strategy->revise(5, '{"cover":"media:1"}', 'media-collection:5:cover-selected');

        self::assertSame(PublicMediaRevisionPromotion::Initial, $policy->decide(null, $stable)->promotion);
        self::assertSame(PublicMediaRevisionPromotion::AlreadyStable, $policy->decide($stable, $stable)->promotion);
        self::assertSame(PublicMediaRevisionPromotion::Advance, $policy->decide($stable, $strategy->revise(6, '{"cover":"media:2"}', 'media-collection:6:cover-selected'))->promotion);
        self::assertSame(PublicMediaRevisionPromotion::Obsolete, $policy->decide($stable, $strategy->revise(4, '{"cover":"media:1"}', 'media-collection:4:uploaded'))->promotion);
        self::assertSame(PublicMediaRevisionPromotion::Divergent, $policy->decide($stable, $strategy->revise(5, '{"cover":"media:other"}', 'media-collection:5:cover-selected'))->promotion);
    }

    public function test_only_initial_and_strictly_newer_revisions_are_promotable(): void
    {
        $strategy = new PublicMediaRevisionStrategy;
        $policy = new PublicMediaRevisionPolicy;
        $stable = $strategy->revise(1, '{"gallery":["media:1"]}', 'media-collection:1:uploaded');

        self::assertTrue($policy->decide(null, $stable)->isPromotable());
        self::assertTrue($policy->decide($stable, $strategy->revise(2, '{"gallery":["media:1","media:2"]}', 'media-collection:2:uploaded'))->isPromotable());
        self::assertFalse($policy->decide($stable, $stable)->isPromotable());
        self::assertFalse($policy->decide($stable, $strategy->revise(1, '{"gallery":[]}', 'media-collection:1:removed'))->isPromotable());
    }

    public function test_revision_integrates_directly_into_existing_vector_watermark_and_readiness(): void
    {
        $revision = (new PublicMediaRevisionStrategy)->revise(9, '{"cover":"media:1"}', 'media-collection:9:published');
        $watermark = new PublicProjectionWatermark(1, 1, 1, 1, 1, 3, $revision->watermarkVersion());

        self::assertSame(9, $watermark->publicMediaVersion);
        self::assertSame(PublicProjectionPromotionReadiness::Ready, $watermark->readiness());
    }

    #[DataProvider('invalidRevisionInputs')]
    public function test_revision_rejects_implicit_or_ambiguous_version_inputs(int $version, string $payload, string $cause): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new PublicMediaRevisionStrategy)->revise($version, $payload, $cause);
    }

    /** @return iterable<string, array{int, string, string}> */
    public static function invalidRevisionInputs(): iterable
    {
        yield 'zero version' => [0, '{"cover":"media:1"}', 'media-collection:0:uploaded'];
        yield 'empty canonical payload' => [1, '', 'media-collection:1:uploaded'];
        yield 'missing causation' => [1, '{"cover":"media:1"}', ''];
    }

    public function test_version_comparison_is_typed_and_contains_no_clock_semantics(): void
    {
        self::assertSame(PublicMediaRevisionRelation::Older, PublicMediaRevisionVersion::fromInt(2)->compareTo(PublicMediaRevisionVersion::fromInt(3)));
        self::assertSame(PublicMediaRevisionRelation::Equal, PublicMediaRevisionVersion::fromInt(3)->compareTo(PublicMediaRevisionVersion::fromInt(3)));
        self::assertSame(PublicMediaRevisionRelation::Newer, PublicMediaRevisionVersion::fromInt(4)->compareTo(PublicMediaRevisionVersion::fromInt(3)));
    }
}
