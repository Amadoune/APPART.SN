<?php

namespace Tests\Unit\PublicGeographySource;

use App\Application\PublicGeographyRevision\PublicGeographyRevisionStrategy;
use App\Application\PublicGeographySource\PublicGeographyBreadcrumbItem;
use App\Application\PublicGeographySource\PublicGeographyDecision;
use App\Application\PublicProjectionStore\PublicProjectionPromotionReadiness;
use App\Application\PublicProjectionStore\PublicProjectionWatermark;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class PublicGeographyDecisionTest extends TestCase
{
    public function test_revision_certifies_exact_content_and_supplies_watermark(): void
    {
        $items = [new PublicGeographyBreadcrumbItem('Dakar', 'https://appart.sn/dakar')];
        $payload = '{"locality":"Dakar","breadcrumb":[{"label":"Dakar","url":"https://appart.sn/dakar"}]}';
        $d = new PublicGeographyDecision('place:dakar', (new PublicGeographyRevisionStrategy)->revise(3, $payload, 'place:3:published'), 'Dakar', $items);
        $w = new PublicProjectionWatermark(1, 1, 1, 1, 1, $d->revision->watermarkVersion(), 1);
        self::assertSame(PublicProjectionPromotionReadiness::Ready, $w->readiness());
        self::assertSame($payload, $d->canonicalPayload());
    }

    public function test_mismatched_content_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new PublicGeographyDecision('place:dakar', (new PublicGeographyRevisionStrategy)->revise(1, '{}', 'cause'), 'Dakar', [new PublicGeographyBreadcrumbItem('Dakar', 'https://appart.sn/dakar')]);
    }
}
