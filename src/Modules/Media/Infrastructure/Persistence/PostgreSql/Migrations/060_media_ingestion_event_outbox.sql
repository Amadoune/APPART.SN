CREATE TABLE IF NOT EXISTS media_ingestion.event_outbox_messages (
    message_id varchar(83) PRIMARY KEY,
    event_id char(64) NOT NULL UNIQUE,
    event_type varchar(128) NOT NULL,
    asset_id uuid NOT NULL,
    aggregate_version integer NOT NULL CHECK (aggregate_version > 0),
    canonical_transport text NOT NULL CHECK (jsonb_typeof(canonical_transport::jsonb) = 'object'),
    payload_checksum char(64) NOT NULL CHECK (payload_checksum ~ '^[0-9a-f]{64}$'),
    occurred_at timestamptz(6) NOT NULL,
    recorded_at timestamptz(6) NOT NULL,
    created_at timestamptz(6) NOT NULL DEFAULT clock_timestamp()
);

CREATE TABLE IF NOT EXISTS media_ingestion.event_outbox_deliveries (
    message_id varchar(83) NOT NULL,
    destination varchar(96) NOT NULL,
    status varchar(24) NOT NULL CHECK (status IN ('pending','claimed','retry_scheduled','delivered','quarantined')),
    attempts integer NOT NULL DEFAULT 0 CHECK (attempts >= 0),
    available_at timestamptz(6) NOT NULL DEFAULT clock_timestamp(),
    claim_owner varchar(96) NULL,
    claimed_until timestamptz(6) NULL,
    delivered_at timestamptz(6) NULL,
    last_error_code varchar(96) NULL,
    PRIMARY KEY (message_id, destination),
    CHECK ((status = 'claimed') = (claim_owner IS NOT NULL AND claimed_until IS NOT NULL))
);

CREATE INDEX IF NOT EXISTS media_ingestion_event_outbox_claim_idx
    ON media_ingestion.event_outbox_deliveries (status, available_at, message_id, destination);
