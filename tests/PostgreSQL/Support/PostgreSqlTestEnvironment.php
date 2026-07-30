<?php

namespace Tests\PostgreSQL\Support;

use PDO;
use RuntimeException;

final class PostgreSqlTestEnvironment
{
    public static function connection(): PDO
    {
        $dsn = getenv('APPART_TEST_PG_DSN');
        if (! is_string($dsn) || $dsn === '') {
            throw new RuntimeException('APPART_TEST_PG_DSN is required; PostgreSQL tests never fall back to another engine.');
        }

        $connection = new PDO(
            $dsn,
            self::environment('APPART_TEST_PG_USER'),
            self::environment('APPART_TEST_PG_PASSWORD'),
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_STRINGIFY_FETCHES => false],
        );
        $version = (string) $connection->query('SHOW server_version')->fetchColumn();
        if (! str_starts_with($version, '18.')) {
            throw new RuntimeException('PostgreSQL 18.x is required for this test suite.');
        }

        return $connection;
    }

    public static function migrate(PDO $connection): void
    {
        $migrations = [
            dirname(__DIR__, 3).'/src/Modules/AdministrationAudit/Infrastructure/Persistence/PostgreSql/Migrations/001_administrative_action.sql',
            dirname(__DIR__, 3).'/src/Modules/ListingLifecycle/Infrastructure/Persistence/PostgreSql/Migrations/002_listing.sql',
            dirname(__DIR__, 3).'/src/Modules/RealEstateCatalog/Infrastructure/Persistence/PostgreSql/Migrations/003_property.sql',
            dirname(__DIR__, 3).'/src/Modules/Media/Infrastructure/Persistence/PostgreSql/Migrations/004_media.sql',
            dirname(__DIR__, 3).'/app/Infrastructure/PublicProjectionOutbox/PostgreSql/Migrations/005_public_projection_outbox.sql',
            dirname(__DIR__, 3).'/app/Infrastructure/PublicProjectionStore/PostgreSql/Migrations/006_public_listing_projection.sql',
            dirname(__DIR__, 3).'/src/Modules/Media/Infrastructure/Persistence/PostgreSql/Migrations/007_media_ownership_lookup.sql',
            dirname(__DIR__, 3).'/src/Modules/SearchDiscovery/Infrastructure/Persistence/PostgreSql/Migrations/008_public_search_decisions.sql',
            dirname(__DIR__, 3).'/src/Modules/ContentSeo/Infrastructure/Persistence/PostgreSql/Migrations/009_content_seo_source_snapshots.sql',
            dirname(__DIR__, 3).'/app/Infrastructure/PublicGeographySource/PostgreSql/Migrations/010_public_geography_decisions.sql',
            dirname(__DIR__, 3).'/app/Infrastructure/PublicMediaSource/PostgreSql/Migrations/011_public_media_decisions.sql',
            dirname(__DIR__, 3).'/src/Modules/ListingLifecycle/Infrastructure/Persistence/PostgreSql/Migrations/012_property_listings_resolution.sql',
            dirname(__DIR__, 3).'/src/Modules/ContentSeo/Infrastructure/Persistence/PostgreSql/Migrations/013_historical_redirect_decisions.sql',
            dirname(__DIR__, 3).'/src/Modules/ContentSeo/Infrastructure/Persistence/PostgreSql/Migrations/014_historical_canonical_qualifications.sql',
            dirname(__DIR__, 3).'/src/Modules/ListingLifecycle/Infrastructure/Persistence/PostgreSql/Migrations/015_listing_publication_workflow.sql',
            dirname(__DIR__, 3).'/app/Infrastructure/ListingPublicationEventRouting/PostgreSql/Migrations/016_listing_publication_event_inbox.sql',
            dirname(__DIR__, 3).'/src/Modules/RealEstateCatalog/Infrastructure/Persistence/PostgreSql/Migrations/017_property_lifecycle_workflow.sql',
            dirname(__DIR__, 3).'/app/Infrastructure/PropertyLifecycleEventRouting/PostgreSql/Migrations/018_property_lifecycle_event_inbox.sql',
            dirname(__DIR__, 3).'/src/Modules/ReservationLifecycle/Infrastructure/Persistence/PostgreSql/Migrations/019_reservation_lifecycle_workflow.sql',
            dirname(__DIR__, 3).'/app/Infrastructure/ReservationLifecycleEventRouting/PostgreSql/Migrations/020_reservation_lifecycle_event_inbox.sql',
            dirname(__DIR__, 3).'/app/Infrastructure/PublicProjectionOutbox/PostgreSql/Migrations/021_reservation_lifecycle_outbox_owner.sql',
            dirname(__DIR__, 3).'/src/Modules/ContactsLeads/Infrastructure/Persistence/PostgreSql/Migrations/022_lead_lifecycle_workflow.sql',
            dirname(__DIR__, 3).'/src/Modules/ContactsLeads/Infrastructure/Persistence/PostgreSql/Migrations/023_lead_eligibility_source_data.sql',
            dirname(__DIR__, 3).'/src/Modules/ContactsLeads/Infrastructure/Persistence/PostgreSql/Migrations/024_lead_lifecycle_context.sql',
            dirname(__DIR__, 3).'/app/Infrastructure/LeadLifecycleEventRouting/PostgreSql/Migrations/025_lead_lifecycle_event_inbox.sql',
            dirname(__DIR__, 3).'/app/Infrastructure/PublicProjectionOutbox/PostgreSql/Migrations/026_contacts_leads_outbox_owner.sql',
            dirname(__DIR__, 3).'/src/Modules/Professionals/Infrastructure/Persistence/PostgreSql/Migrations/027_professional_status_workflow.sql',
            dirname(__DIR__, 3).'/src/Modules/Professionals/Infrastructure/Persistence/PostgreSql/Migrations/028_professional_status_context.sql',
            dirname(__DIR__, 3).'/app/Infrastructure/ProfessionalStatusEventRouting/PostgreSql/Migrations/029_professional_status_event_inbox.sql',
            dirname(__DIR__, 3).'/app/Infrastructure/PublicProjectionOutbox/PostgreSql/Migrations/030_professionals_outbox_owner.sql',
            dirname(__DIR__, 3).'/src/Modules/Media/Infrastructure/Persistence/PostgreSql/Migrations/031_media_item_lifecycle_workflow.sql',
            dirname(__DIR__, 3).'/src/Modules/Media/Infrastructure/Persistence/PostgreSql/Migrations/032_media_item_lifecycle_context.sql',
            dirname(__DIR__, 3).'/app/Infrastructure/MediaItemLifecycleEventRouting/PostgreSql/Migrations/033_media_item_lifecycle_event_inbox.sql',
            dirname(__DIR__, 3).'/src/Modules/AdministrationAudit/Infrastructure/Persistence/PostgreSql/Migrations/034_administrative_action_lifecycle_workflow.sql',
            dirname(__DIR__, 3).'/src/Modules/AdministrationAudit/Infrastructure/Persistence/PostgreSql/Migrations/035_administrative_action_lifecycle_context.sql',
            dirname(__DIR__, 3).'/app/Infrastructure/AdministrativeActionLifecycleEventRouting/PostgreSql/Migrations/036_administrative_action_lifecycle_event_inbox.sql',
            dirname(__DIR__, 3).'/app/Infrastructure/PublicProjectionOutbox/PostgreSql/Migrations/037_administration_audit_outbox_owner.sql',
            dirname(__DIR__, 3).'/src/Modules/Geography/Infrastructure/Persistence/PostgreSql/Migrations/038_place_lifecycle_workflow.sql',
            dirname(__DIR__, 3).'/app/Infrastructure/PlaceLifecycleEventRouting/PostgreSql/Migrations/039_place_lifecycle_event_inbox.sql',
            dirname(__DIR__, 3).'/app/Infrastructure/PublicProjectionOutbox/PostgreSql/Migrations/040_geography_outbox_owner.sql',
            dirname(__DIR__, 3).'/src/Modules/IdentityAccess/Infrastructure/Persistence/PostgreSql/Migrations/041_account_status_lifecycle_workflow.sql',
            dirname(__DIR__, 3).'/src/Modules/IdentityAccess/Infrastructure/Persistence/PostgreSql/Migrations/042_historical_account_persistence.sql',
            dirname(__DIR__, 3).'/app/Infrastructure/PublicProjectionOutbox/PostgreSql/Migrations/043_identity_access_outbox_owner.sql',
            dirname(__DIR__, 3).'/src/Modules/IdentityAccess/Infrastructure/Persistence/PostgreSql/Migrations/044_authentication_attempts.sql',
            dirname(__DIR__, 3).'/src/Modules/IdentityAccess/Infrastructure/Persistence/PostgreSql/Migrations/045_sessions.sql',
            dirname(__DIR__, 3).'/src/Modules/IdentityAccess/Infrastructure/Persistence/PostgreSql/Migrations/046_password_recovery.sql',
            dirname(__DIR__, 3).'/src/Modules/IdentityAccess/Infrastructure/Persistence/PostgreSql/Migrations/047_user_profiles.sql',
            dirname(__DIR__, 3).'/src/Modules/IdentityAccess/Infrastructure/Persistence/PostgreSql/Migrations/048_identity_claims.sql',
            dirname(__DIR__, 3).'/src/Modules/IdentityAccess/Infrastructure/Persistence/PostgreSql/Migrations/049_pending_contact_changes.sql',
            dirname(__DIR__, 3).'/src/Modules/IdentityAccess/Infrastructure/Persistence/PostgreSql/Migrations/050_profile_revisions.sql',
            dirname(__DIR__, 3).'/src/Modules/IdentityAccess/Infrastructure/Persistence/PostgreSql/Migrations/051_account_closures.sql',
            dirname(__DIR__, 3).'/src/Modules/IdentityAccess/Infrastructure/Persistence/PostgreSql/Migrations/052_profile_claims_authority_cutover.sql',
            dirname(__DIR__, 3).'/src/Modules/IdentityAccess/Infrastructure/Persistence/PostgreSql/Migrations/053_identity_access_atomic_operations.sql',
            dirname(__DIR__, 3).'/src/Modules/IdentityAccess/Infrastructure/Persistence/PostgreSql/Migrations/054_identity_access_event_outbox.sql',
            dirname(__DIR__, 3).'/src/Modules/ListingLifecycle/Infrastructure/Persistence/PostgreSql/Migrations/055_listing_creation_intents.sql',
            dirname(__DIR__, 3).'/src/Modules/RealEstateCatalog/Infrastructure/Persistence/PostgreSql/Migrations/056_property_authoring.sql',
            dirname(__DIR__, 3).'/src/Modules/ListingLifecycle/Infrastructure/Persistence/PostgreSql/Migrations/057_listing_authoring.sql',
            dirname(__DIR__, 3).'/src/Modules/Media/Infrastructure/Persistence/PostgreSql/Migrations/058_media_ingestion.sql',
            dirname(__DIR__, 3).'/src/Modules/Media/Infrastructure/Persistence/PostgreSql/Migrations/059_media_attachment_intents.sql',
            dirname(__DIR__, 3).'/src/Modules/Media/Infrastructure/Persistence/PostgreSql/Migrations/060_media_ingestion_event_outbox.sql',
            dirname(__DIR__, 3).'/src/Modules/Professionals/Infrastructure/Persistence/PostgreSql/Migrations/061_professional_profile.sql',
            dirname(__DIR__, 3).'/src/Modules/Professionals/Infrastructure/Persistence/PostgreSql/Migrations/062_professional_mandate_owner_source.sql',
            dirname(__DIR__, 3).'/src/Modules/ModerationReports/Infrastructure/Persistence/PostgreSql/Migrations/063_moderation_reports.sql',
            dirname(__DIR__, 3).'/src/Modules/ModerationReports/Infrastructure/Persistence/PostgreSql/Migrations/064_moderation_queue_claim_intents.sql',
            dirname(__DIR__, 3).'/src/Modules/ModerationReports/Infrastructure/Persistence/PostgreSql/Migrations/065_moderation_event_delivery.sql',
            dirname(__DIR__, 3).'/src/Modules/ModerationReports/Infrastructure/Persistence/PostgreSql/Migrations/066_moderation_atomic_outbox_appends.sql',
            dirname(__DIR__, 3).'/src/Modules/ModerationReports/Infrastructure/Persistence/PostgreSql/Migrations/067_moderation_event_outbox.sql',
            dirname(__DIR__, 3).'/src/Modules/ListingLifecycle/Infrastructure/Persistence/PostgreSql/Migrations/068_listing_moderation_intents.sql',
            dirname(__DIR__, 3).'/src/Modules/ModerationReports/Infrastructure/Persistence/PostgreSql/Migrations/069_moderation_listing_handoff_results.sql',
            dirname(__DIR__, 3).'/src/Modules/ModerationReports/Infrastructure/Persistence/PostgreSql/Migrations/070_moderation_queue_owner_read_source.sql',
            dirname(__DIR__, 3).'/src/Modules/AdministrationAudit/Infrastructure/Persistence/PostgreSql/Migrations/071_administration_audit_public_append.sql',
        ];
        foreach ($migrations as $migration) {
            $sql = file_get_contents($migration);
            if (! is_string($sql)) {
                throw new RuntimeException('A PostgreSQL slice migration is unreadable.');
            }
            $connection->exec($sql);
        }
    }

    public static function reset(PDO $connection): void
    {
        $connection->exec('TRUNCATE administration_audit.public_append_records');
        $connection->exec('TRUNCATE moderation_reports.listing_handoff_results, moderation_reports.outbox_deliveries, moderation_reports.outbox_messages, moderation_reports.atomic_outbox_appends, moderation_reports.event_deliveries, moderation_reports.queue_claim_intents, moderation_reports.queue_checkpoints, moderation_reports.queue_items, moderation_reports.case_intents, moderation_reports.decision_supersessions, moderation_reports.decision_revisions, moderation_reports.finding_revisions, moderation_reports.report_revisions, moderation_reports.cases');
        $connection->exec('TRUNCATE listing_lifecycle.moderation_command_intent_results, listing_lifecycle.moderation_command_intents');
        $connection->exec('TRUNCATE professional_core.mandate_owner_source_intents, professional_core.mandate_owner_sources');
        $connection->exec('TRUNCATE professional_profile.public_portfolio_intents, professional_profile.public_portfolios, professional_profile.verification_intents, professional_profile.verification_decisions, professional_profile.verifications, professional_profile.public_profile_intents, professional_profile.public_profile_revisions, professional_profile.public_profiles');
        $connection->exec('TRUNCATE media_ingestion.event_outbox_deliveries, media_ingestion.event_outbox_messages');
        $connection->exec('TRUNCATE media_ingestion.upload_intents, media_ingestion.asset_intents, media_ingestion.processing_intents, media_ingestion.quota_intents, media_ingestion.uploads, media_ingestion.assets, media_ingestion.processing, media_ingestion.quotas');
        $connection->exec('TRUNCATE media.media_attachment_intents');
        $connection->exec('TRUNCATE listing_lifecycle.listing_creation_intents');
        $connection->exec('TRUNCATE listing_authoring.portfolio_items, listing_authoring.delegations, listing_authoring.ownerships, listing_authoring.draft_revisions, listing_authoring.drafts, real_estate_catalog_authoring.property_authoring');
        $connection->exec('TRUNCATE identity_access_completion.event_outbox_deliveries, identity_access_completion.event_outbox_messages, identity_access_completion.atomic_operation_intents, identity_access_completion.profile_claim_seed_quarantine, identity_access_completion.profile_claim_seed_manifest, identity_access_completion.profile_claim_seed_runs, identity_access_completion.authentication_attempts, identity_access_completion.sessions, identity_access_completion.session_invalidation_checkpoints, identity_access_completion.recovery_challenges, identity_access_completion.user_profiles, identity_access_completion.identity_claims, identity_access_completion.pending_contact_changes, identity_access_completion.profile_revisions, identity_access_completion.account_closures');
        $connection->exec("UPDATE identity_access_completion.profile_claim_authority SET authority='Historical',generation=0,active_run_id=NULL,changed_at=TIMESTAMPTZ '1970-01-01 00:00:00+00',last_intent_id='00000000-0000-0000-0000-000000000000',last_intent_checksum=repeat('0',64) WHERE authority_key='ProfileClaims'");
        $connection->exec('TRUNCATE identity_access.accounts CASCADE');
        $connection->exec('TRUNCATE identity_access.account_status_lifecycle_transitions');
        $connection->exec('TRUNCATE geography.place_lifecycle_transitions');
        $connection->exec('TRUNCATE geography.place_lifecycle_event_inbox');
        $connection->exec('TRUNCATE administration_audit.administrative_action_lifecycle_event_inbox');
        $connection->exec('TRUNCATE administration_audit.administrative_action_lifecycle_transition_contexts');
        $connection->exec('TRUNCATE content_seo.historical_redirect_decisions');
        $connection->exec('TRUNCATE content_seo.historical_canonical_qualifications');
        $connection->exec('TRUNCATE listing_lifecycle.publication_workflow_transitions');
        $connection->exec('TRUNCATE listing_lifecycle.publication_event_inbox');
        $connection->exec('TRUNCATE real_estate_catalog.property_lifecycle_transitions');
        $connection->exec('TRUNCATE real_estate_catalog.property_lifecycle_event_inbox');
        $connection->exec('TRUNCATE reservation_lifecycle.reservation_lifecycle_transitions');
        $connection->exec('TRUNCATE reservation_lifecycle.reservation_lifecycle_event_inbox');
        $connection->exec('TRUNCATE contacts_leads.lead_lifecycle_transition_contexts, contacts_leads.lead_lifecycle_transitions');
        $connection->exec('TRUNCATE contacts_leads.lead_lifecycle_event_inbox');
        $connection->exec('TRUNCATE contacts_leads.lead_eligibility_decisions');
        $connection->exec('TRUNCATE professionals.professional_status_transition_contexts, professionals.professional_status_transitions');
        $connection->exec('TRUNCATE professionals.professional_status_event_inbox');
        $connection->exec('TRUNCATE media.media_item_lifecycle_transition_contexts, media.media_item_lifecycle_transitions');
        $connection->exec('TRUNCATE media.media_item_lifecycle_event_inbox');
        $connection->exec('TRUNCATE administration_audit.administrative_action_lifecycle_transitions');
        $connection->exec('TRUNCATE search_discovery.public_search_decisions');
        $connection->exec('TRUNCATE content_seo.public_source_snapshots');
        $connection->exec('TRUNCATE public_geography.decisions');
        $connection->exec('TRUNCATE public_media.decisions');
        $connection->exec('TRUNCATE public_projection.listing_projections, public_projection.generations CASCADE');
        foreach (['listing_lifecycle', 'real_estate_catalog', 'media', 'search_discovery', 'content_seo', 'reservation_lifecycle', 'contacts_leads', 'professionals', 'administration_audit', 'geography', 'identity_access'] as $schema) {
            $connection->exec("TRUNCATE {$schema}.public_projection_outbox_replays, {$schema}.public_projection_outbox_cursors, {$schema}.public_projection_outbox_deliveries, {$schema}.public_projection_outbox_messages CASCADE");
        }
        $connection->exec('TRUNCATE media.media_items, media.media_id_reservations, media.media_collections, real_estate_catalog.property_addresses, real_estate_catalog.property_reference_reservations, real_estate_catalog.properties, listing_lifecycle.listing_revisions, listing_lifecycle.listings, administration_audit.administrative_action_audit_entries, administration_audit.administrative_action_decisions, administration_audit.administrative_action_approvals, administration_audit.administrative_actions');
    }

    private static function environment(string $name): string
    {
        $value = getenv($name);

        return is_string($value) ? $value : '';
    }
}
