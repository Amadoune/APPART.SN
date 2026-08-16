<?php

namespace Tests\Unit\PublicGeographySource;

use App\Application\PublicGeographyRevision\PublicGeographyRevisionStrategy;
use App\Application\PublicGeographySource\PublicGeographyBreadcrumbItem;
use App\Application\PublicGeographySource\PublicGeographyBreadcrumbItemV2;
use App\Application\PublicGeographySource\PublicGeographyDecision;
use App\Application\PublicGeographySource\PublicGeographyDecisionStatusV2;
use App\Application\PublicGeographySource\PublicGeographyDecisionV2;
use App\Application\PublicGeographySource\PublicGeographyRevisionVectorItemV2;
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

    public function test_v2_is_deterministic_and_contains_no_url_or_slug(): void
    {
        $payload = '{"schemaVersion":"public-geography-place-representation-v2","terminalPlaceId":"city:dakar","status":"available","locality":"Dakar","breadcrumb":[{"placeId":"country:sn","type":"country","officialName":"Senegal","parentPlaceId":null,"aggregateVersion":2},{"placeId":"city:dakar","type":"city","officialName":"Dakar","parentPlaceId":"country:sn","aggregateVersion":4}],"revisionVector":[{"placeId":"country:sn","aggregateVersion":2},{"placeId":"city:dakar","aggregateVersion":4}]}';
        $decision = new PublicGeographyDecisionV2(
            'city:dakar',
            PublicGeographyDecisionStatusV2::Available,
            'Dakar',
            [
                new PublicGeographyBreadcrumbItemV2('country:sn', 'country', 'Senegal', null, 2),
                new PublicGeographyBreadcrumbItemV2('city:dakar', 'city', 'Dakar', 'country:sn', 4),
            ],
            [
                new PublicGeographyRevisionVectorItemV2('country:sn', 2),
                new PublicGeographyRevisionVectorItemV2('city:dakar', 4),
            ],
            (new PublicGeographyRevisionStrategy)->revise(6, $payload, 'geography:v2:test'),
        );

        self::assertSame($payload, $decision->canonicalPayload());
        self::assertStringNotContainsString('url', strtolower($decision->canonicalPayload()));
        self::assertStringNotContainsString('slug', strtolower($decision->canonicalPayload()));
    }
}
