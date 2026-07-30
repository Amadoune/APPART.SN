<?php

namespace Tests\Unit\PublicProjectionOutbox;

use App\Application\PublicProjectionDelivery\PublicProjectionDeliverySourceModule;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlPublicProjectionOutboxSchema;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class ReservationLifecycleOutboxOwnerSchemaTest extends TestCase
{
    public function test_eleven_owner_modules_resolve_to_their_exact_schema(): void
    {
        $matrix = [
            'ListingLifecycle' => 'listing_lifecycle',
            'RealEstateCatalog' => 'real_estate_catalog',
            'Media' => 'media',
            'SearchDiscovery' => 'search_discovery',
            'ContentSeo' => 'content_seo',
            'ReservationLifecycle' => 'reservation_lifecycle',
            'ContactsLeads' => 'contacts_leads',
            'Professionals' => 'professionals',
            'AdministrationAudit' => 'administration_audit',
            'Geography' => 'geography',
            'IdentityAccess' => 'identity_access',
        ];

        foreach ($matrix as $module => $schema) {
            self::assertSame($schema, PostgreSqlPublicProjectionOutboxSchema::for(PublicProjectionDeliverySourceModule::fromString($module)));
            self::assertSame($module, PostgreSqlPublicProjectionOutboxSchema::moduleFor($schema)->value);
        }
        self::assertSame(array_values($matrix), PostgreSqlPublicProjectionOutboxSchema::all());
    }

    public function test_unknown_owner_schema_is_rejected_explicitly(): void
    {
        $this->expectException(InvalidArgumentException::class);
        PostgreSqlPublicProjectionOutboxSchema::moduleFor('unknown_owner');
    }
}
