<?php

namespace Tests\Unit\PropertyAuthoringPublicSurface;

use Appart\Modules\RealEstateCatalog\Infrastructure\Persistence\PropertyAuthoringMapper;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PropertyAuthoringMapperTest extends TestCase
{
    #[Test]
    public function it_reconstructs_the_three_owner_scoped_public_authoring_fields(): void
    {
        $state = (new PropertyAuthoringMapper)->toState([
            'property_id' => '94000000-0000-4000-8000-000000000001',
            'owner_account_id' => '94000000-0000-4000-8000-000000000002',
            'version' => 1,
            'last_intent_id' => '94000000-0000-4000-8000-000000000003',
            'last_intent_checksum' => hash('sha256', 'p05-property'),
            'property_type' => 'apartment',
            'city' => 'Dakar',
            'neighborhood' => 'Almadies',
        ]);

        self::assertSame('apartment', $state->propertyType);
        self::assertSame('Dakar', $state->city);
        self::assertSame('Almadies', $state->neighborhood);
    }

    #[Test]
    public function historical_rows_remain_readable_as_unqualified(): void
    {
        $state = (new PropertyAuthoringMapper)->toState([
            'property_id' => '94000000-0000-4000-8000-000000000001',
            'owner_account_id' => '94000000-0000-4000-8000-000000000002',
            'version' => 1,
            'last_intent_id' => '94000000-0000-4000-8000-000000000003',
            'last_intent_checksum' => hash('sha256', 'historical'),
        ]);

        self::assertNull($state->propertyType);
        self::assertNull($state->city);
        self::assertNull($state->neighborhood);
    }
}
