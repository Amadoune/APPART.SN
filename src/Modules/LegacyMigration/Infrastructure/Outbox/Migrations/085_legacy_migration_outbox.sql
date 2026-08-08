CREATE TABLE IF NOT EXISTS legacy_migration.outbox_messages (
    message_id char(64) PRIMARY KEY CHECK (message_id ~ '^[0-9a-f]{64}$'),
    event_type varchar(64) NOT NULL CHECK (event_type IN ('legacy-migration.inventory.observed.v1','legacy-migration.wave.observed.v1','legacy-migration.reconciliation.observed.v1','legacy-migration.quarantine.observed.v1','legacy-migration.cutover.observed.v1')),
    delivery_status varchar(32) NOT NULL CHECK (delivery_status IN ('available','ready','blocked','completed','matched','divergent','pending','empty','contains_items','missing','corrupted','dependency_unavailable')),
    observed_at timestamptz(6) NOT NULL,
    payload jsonb NOT NULL CHECK (jsonb_typeof(payload) = 'object'),
    message_checksum char(64) NOT NULL CHECK (message_checksum ~ '^[0-9a-f]{64}$'),
    retry_count smallint NOT NULL DEFAULT 0 CHECK (retry_count >= 0 AND retry_count <= 10),
    created_at timestamptz(6) NOT NULL DEFAULT clock_timestamp(),
    delivered_at timestamptz(6) NULL
);
CREATE INDEX IF NOT EXISTS legacy_migration_outbox_pending_idx ON legacy_migration.outbox_messages(created_at,message_id) WHERE delivered_at IS NULL AND retry_count < 10;
