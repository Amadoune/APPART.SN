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
        self::assertSafeTestDatabase($connection);

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
            dirname(__DIR__, 3).'/src/Modules/ContactsLeads/Infrastructure/Persistence/PostgreSql/Migrations/072_consent_owner_local_source.sql',
            dirname(__DIR__, 3).'/src/Modules/ListingLifecycle/Infrastructure/Persistence/PostgreSql/Migrations/092_listing_transition_reason_optional.sql',
            dirname(__DIR__, 3).'/src/Modules/ListingLifecycle/Infrastructure/Persistence/PostgreSql/Migrations/093_authoring_public_fact_handoff.sql',
            dirname(__DIR__, 3).'/src/Modules/RealEstateCatalog/Infrastructure/Persistence/PostgreSql/Migrations/094_property_authoring_public_surface.sql',
            dirname(__DIR__, 3).'/src/Modules/IdentityAccess/Infrastructure/Persistence/PostgreSql/Migrations/095_iam_session_policy_authority.sql',
            dirname(__DIR__, 3).'/src/Modules/PublicationReview/Infrastructure/Persistence/PostgreSql/Migrations/096_publication_review_queue.sql',
            dirname(__DIR__, 3).'/src/Modules/ListingLifecycle/Infrastructure/Persistence/PostgreSql/Migrations/097_listing_publication_command_gateway.sql',
            dirname(__DIR__, 3).'/src/Modules/Geography/Infrastructure/Persistence/PostgreSql/Migrations/098_geography_places.sql',
            dirname(__DIR__, 3).'/src/Modules/RealEstateCatalog/Infrastructure/Persistence/PostgreSql/Migrations/099_property_authoring_source_completeness.sql',
            dirname(__DIR__, 3).'/src/Modules/RealEstateCatalog/Infrastructure/Persistence/PostgreSql/Migrations/100_property_promotion.sql',
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
        self::assertSafeTestDatabase($connection);
        $connection->exec('TRUNCATE publication_review.command_ledger, publication_review.queue_items');
        $connection->exec('TRUNCATE listing_lifecycle.publication_command_gateway_ledger');
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
        $connection->exec('TRUNCATE geography.places CASCADE');
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
        $connection->exec('TRUNCATE contacts_leads.consent_decision_revisions');
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
        $connection->exec('TRUNCATE real_estate_catalog.property_promotion_commands, listing_lifecycle.authoring_public_fact_handoffs, media.media_items, media.media_id_reservations, media.media_collections, real_estate_catalog.property_addresses, real_estate_catalog.property_reference_reservations, real_estate_catalog.properties, listing_lifecycle.listing_revisions, listing_lifecycle.listings, administration_audit.administrative_action_audit_entries, administration_audit.administrative_action_decisions, administration_audit.administrative_action_approvals, administration_audit.administrative_actions');
    }

    public static function assertDatabaseNamesAreIsolated(string $testDatabase, string $applicationDatabase): void
    {
        if (preg_match('/(?:^|_)test(?:$|_)/i', $testDatabase) !== 1) {
            throw new RuntimeException('PostgreSQL destructive tests require an explicitly test-only database name.');
        }
        if ($applicationDatabase === '') {
            throw new RuntimeException('The local application PostgreSQL database must be declared before destructive tests can run.');
        }
        if (hash_equals(strtolower($applicationDatabase), strtolower($testDatabase))) {
            throw new RuntimeException('PostgreSQL tests refuse to use the local application database.');
        }
    }

    private static function assertSafeTestDatabase(PDO $connection): void
    {
        $database = $connection->query('SELECT current_database()')->fetchColumn();
        if (! is_string($database) || $database === '') {
            throw new RuntimeException('The PostgreSQL test database identity is unavailable.');
        }

        self::assertDatabaseNamesAreIsolated($database, self::applicationDatabase());
    }

    private static function applicationDatabase(): string
    {
        $configured = getenv('APPART_APPLICATION_PG_DATABASE');
        if (is_string($configured) && $configured !== '') {
            return $configured;
        }

        $environment = dirname(__DIR__, 3).'/.env';
        $contents = is_file($environment) ? file($environment, FILE_IGNORE_NEW_LINES) : false;
        if (is_array($contents)) {
            foreach ($contents as $line) {
                if (str_starts_with($line, 'DB_DATABASE=')) {
                    return trim(substr($line, strlen('DB_DATABASE=')), " \t\n\r\0\x0B\"'");
                }
            }
        }

        return '';
    }

    private static function environment(string $name): string
    {
        $value = getenv($name);

        return is_string($value) ? $value : '';
    }
}
