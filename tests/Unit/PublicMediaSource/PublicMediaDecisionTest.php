<?php

namespace Tests\Unit\PublicMediaSource;

use App\Application\PublicMediaRevision\PublicMediaRevisionStrategy;
use App\Application\PublicMediaSource\PublicMediaDecision;
use App\Application\PublicProjectionStore\PublicProjectionPromotionReadiness;
use App\Application\PublicProjectionStore\PublicProjectionWatermark;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Tests\Support\PublicMediaDecisionFixture;

final class PublicMediaDecisionTest extends TestCase
{
    public function test_revision_certifies_exact_ordered_content_and_completes_watermark(): void
    {
        $decision = PublicMediaDecisionFixture::make();
        self::assertSame(hash('sha256', $decision->canonicalPayload()), $decision->revision->checksum->value);
        self::assertSame('media:cover', $decision->cover?->mediaId);
        self::assertSame(['media:cover', 'media:gallery-2'], array_map(static fn ($item) => $item->mediaId, $decision->gallery));

        $watermark = new PublicProjectionWatermark(1, 1, 1, 1, 1, 1, $decision->revision->watermarkVersion());
        self::assertSame(PublicProjectionPromotionReadiness::Ready, $watermark->readiness());
    }

    public function test_revision_for_another_payload_is_rejected(): void
    {
        $valid = PublicMediaDecisionFixture::make();
        $this->expectException(InvalidArgumentException::class);
        new PublicMediaDecision(
            $valid->mediaCollectionId,
            (new PublicMediaRevisionStrategy)->revise(1, '{"cover":null,"gallery":[]}', 'media-collection:1:published'),
            $valid->cover,
            $valid->gallery,
        );
    }
}
