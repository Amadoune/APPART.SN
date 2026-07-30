<?php

namespace Tests\Unit\ListingLifecycle\ModerationBoundary;

use Appart\Modules\ListingLifecycle\Application\ModerationBoundary\ListingModerationActionV1;
use Appart\Modules\ListingLifecycle\Application\ModerationBoundary\ListingModerationCommandResultV1;
use Appart\Modules\ListingLifecycle\Application\ModerationBoundary\ListingModerationEligibilityV1;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ListingModerationBoundaryContractTest extends TestCase
{
    #[Test]
    public function reader_and_gateway_catalogs_are_closed(): void
    {
        self::assertSame([
            'eligible',
            'ineligible',
            'missing',
            'corrupted',
            'dependency_unavailable',
        ], array_column(ListingModerationEligibilityV1::cases(), 'value'));
        self::assertSame([
            'suspend',
            'request_changes',
            'reject',
            'archive',
        ], array_column(ListingModerationActionV1::cases(), 'value'));
        self::assertSame([
            'applied',
            'already_applied',
            'rejected',
            'divergent_intent',
            'version_conflict',
            'dependency_unavailable',
        ], array_column(ListingModerationCommandResultV1::cases(), 'value'));
    }
}
