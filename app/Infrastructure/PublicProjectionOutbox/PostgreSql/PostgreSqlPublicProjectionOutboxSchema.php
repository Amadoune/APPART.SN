<?php

namespace App\Infrastructure\PublicProjectionOutbox\PostgreSql;

use App\Application\PublicProjectionDelivery\PublicProjectionDeliverySourceModule;
use InvalidArgumentException;

final class PostgreSqlPublicProjectionOutboxSchema
{
    public static function for(PublicProjectionDeliverySourceModule $module): string
    {
        return match ($module->value) {
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
            default => throw new InvalidArgumentException('Unsupported Public Projection Outbox owner module.'),
        };
    }

    public static function moduleFor(string $schema): PublicProjectionDeliverySourceModule
    {
        return PublicProjectionDeliverySourceModule::fromString(match ($schema) {
            'listing_lifecycle' => 'ListingLifecycle',
            'real_estate_catalog' => 'RealEstateCatalog',
            'media' => 'Media',
            'search_discovery' => 'SearchDiscovery',
            'content_seo' => 'ContentSeo',
            'reservation_lifecycle' => 'ReservationLifecycle',
            'contacts_leads' => 'ContactsLeads',
            'professionals' => 'Professionals',
            'administration_audit' => 'AdministrationAudit',
            'geography' => 'Geography',
            'identity_access' => 'IdentityAccess',
            default => throw new InvalidArgumentException('Unsupported Public Projection Outbox owner schema.'),
        });
    }

    /** @return list<string> */
    public static function all(): array
    {
        return ['listing_lifecycle', 'real_estate_catalog', 'media', 'search_discovery', 'content_seo', 'reservation_lifecycle', 'contacts_leads', 'professionals', 'administration_audit', 'geography', 'identity_access'];
    }
}
