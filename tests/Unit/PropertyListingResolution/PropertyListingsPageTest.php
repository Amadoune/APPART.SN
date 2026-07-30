<?php

namespace Tests\Unit\PropertyListingResolution;

use App\Application\PropertyListingResolution\PropertyListingsDiagnostic;
use App\Application\PropertyListingResolution\PropertyListingsPage;
use App\Application\PropertyListingResolution\PropertyListingsPageStatus;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PropertyListingsPageTest extends TestCase
{
    #[DataProvider('validPages')]
    public function test_closed_result_model_accepts_each_valid_shape(PropertyListingsPage $page): void
    {
        self::assertContains($page->status, PropertyListingsPageStatus::cases());
    }

    public static function validPages(): array
    {
        return [
            [new PropertyListingsPage(PropertyListingsPageStatus::Found, ['9a000000-0000-4000-8000-000000000001'], 'opaque', false)],
            [new PropertyListingsPage(PropertyListingsPageStatus::Empty, [], null, true)],
            [new PropertyListingsPage(PropertyListingsPageStatus::Completed, ['9a000000-0000-4000-8000-000000000001'], null, true)],
            [new PropertyListingsPage(PropertyListingsPageStatus::InvalidIdentity, [], null, true, PropertyListingsDiagnostic::InvalidPropertyIdentity)],
            [new PropertyListingsPage(PropertyListingsPageStatus::Corrupted, [], null, true, PropertyListingsDiagnostic::InvalidCheckpoint)],
        ];
    }

    public function test_an_inconsistent_result_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new PropertyListingsPage(PropertyListingsPageStatus::Found, [], null, true);
    }
}
