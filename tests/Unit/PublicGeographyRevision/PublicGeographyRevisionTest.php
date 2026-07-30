<?php

namespace Tests\Unit\PublicGeographyRevision;

use App\Application\PublicGeographyRevision\PublicGeographyRevisionPolicy;
use App\Application\PublicGeographyRevision\PublicGeographyRevisionPromotion;
use App\Application\PublicGeographyRevision\PublicGeographyRevisionRelation;
use App\Application\PublicGeographyRevision\PublicGeographyRevisionStrategy;
use App\Application\PublicGeographyRevision\PublicGeographyRevisionVersion;
use App\Application\PublicProjectionStore\PublicProjectionPromotionReadiness;
use App\Application\PublicProjectionStore\PublicProjectionWatermark;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PublicGeographyRevisionTest extends TestCase
{
    public function test_same_explicit_inputs_always_produce_the_same_stable_revision(): void
    {
        $strategy = new PublicGeographyRevisionStrategy;
        $first = $strategy->revise(7, '{"country":"SN","place":"Dakar","enabled":true}', 'place:7:renamed');
        $second = $strategy->revise(7, '{"country":"SN","place":"Dakar","enabled":true}', 'place:7:renamed');

        self::assertTrue($first->sameFactAs($second));
        self::assertSame(7, $first->watermarkVersion());
    }

    public function test_public_payload_change_is_explicit_and_reproducible(): void
    {
        $strategy = new PublicGeographyRevisionStrategy;
        $previous = $strategy->revise(7, '{"name":"Dakar"}', 'place:7:renamed');
        $changed = $strategy->revise(8, '{"name":"Dakar Plateau"}', 'place:8:renamed');

        self::assertNotSame($previous->checksum->value, $changed->checksum->value);
        self::assertSame($changed->checksum->value, $strategy->revise(8, '{"name":"Dakar Plateau"}', 'place:8:renamed')->checksum->value);
    }

    public function test_promotion_decisions_are_exhaustive_and_deterministic(): void
    {
        $strategy = new PublicGeographyRevisionStrategy;
        $policy = new PublicGeographyRevisionPolicy;
        $stable = $strategy->revise(5, '{"name":"Dakar"}', 'place:5:renamed');

        self::assertSame(PublicGeographyRevisionPromotion::Initial, $policy->decide(null, $stable)->promotion);
        self::assertSame(PublicGeographyRevisionPromotion::AlreadyStable, $policy->decide($stable, $stable)->promotion);
        self::assertSame(PublicGeographyRevisionPromotion::Advance, $policy->decide($stable, $strategy->revise(6, '{"name":"Plateau"}', 'place:6:renamed'))->promotion);
        self::assertSame(PublicGeographyRevisionPromotion::Obsolete, $policy->decide($stable, $strategy->revise(4, '{"name":"Dakar"}', 'place:4:created'))->promotion);
        self::assertSame(PublicGeographyRevisionPromotion::Divergent, $policy->decide($stable, $strategy->revise(5, '{"name":"Different"}', 'place:5:renamed'))->promotion);
    }

    public function test_only_initial_and_strictly_newer_revisions_are_promotable(): void
    {
        $strategy = new PublicGeographyRevisionStrategy;
        $policy = new PublicGeographyRevisionPolicy;
        $stable = $strategy->revise(1, '{"name":"Dakar"}', 'place:1:created');

        self::assertTrue($policy->decide(null, $stable)->isPromotable());
        self::assertTrue($policy->decide($stable, $strategy->revise(2, '{"name":"Plateau"}', 'place:2:renamed'))->isPromotable());
        self::assertFalse($policy->decide($stable, $stable)->isPromotable());
        self::assertFalse($policy->decide($stable, $strategy->revise(1, '{"name":"Other"}', 'place:1:renamed'))->isPromotable());
    }

    public function test_revision_integrates_directly_into_existing_vector_watermark_and_readiness(): void
    {
        $revision = (new PublicGeographyRevisionStrategy)->revise(9, '{"name":"Dakar"}', 'place:9:enabled');
        $watermark = new PublicProjectionWatermark(1, 1, 1, 1, 1, $revision->watermarkVersion(), 3);

        self::assertSame(9, $watermark->publicGeographyVersion);
        self::assertSame(PublicProjectionPromotionReadiness::Ready, $watermark->readiness());
    }

    #[DataProvider('invalidRevisionInputs')]
    public function test_revision_rejects_implicit_or_ambiguous_version_inputs(int $version, string $payload, string $cause): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new PublicGeographyRevisionStrategy)->revise($version, $payload, $cause);
    }

    /** @return iterable<string, array{int, string, string}> */
    public static function invalidRevisionInputs(): iterable
    {
        yield 'zero version' => [0, '{"name":"Dakar"}', 'place:0:created'];
        yield 'empty canonical payload' => [1, '', 'place:1:created'];
        yield 'missing causation' => [1, '{"name":"Dakar"}', ''];
    }

    public function test_version_comparison_is_numeric_and_contains_no_clock_semantics(): void
    {
        self::assertSame(PublicGeographyRevisionRelation::Older, PublicGeographyRevisionVersion::fromInt(2)->compareTo(PublicGeographyRevisionVersion::fromInt(3)));
        self::assertSame(PublicGeographyRevisionRelation::Equal, PublicGeographyRevisionVersion::fromInt(3)->compareTo(PublicGeographyRevisionVersion::fromInt(3)));
        self::assertSame(PublicGeographyRevisionRelation::Newer, PublicGeographyRevisionVersion::fromInt(4)->compareTo(PublicGeographyRevisionVersion::fromInt(3)));
    }
}
