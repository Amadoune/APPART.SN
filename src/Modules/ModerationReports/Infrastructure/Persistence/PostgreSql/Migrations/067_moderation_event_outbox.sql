CREATE TABLE IF NOT EXISTS moderation_reports.outbox_messages (
    message_id varchar(78) PRIMARY KEY,
    event_id uuid NOT NULL UNIQUE,
    event_type varchar(128) NOT NULL,
    case_id uuid NOT NULL,
    aggregate_version integer NOT NULL CHECK (aggregate_version > 0),
    canonical_transport jsonb NOT NULL CHECK (jsonb_typeof(canonical_transport) = 'object'),
    payload_checksum char(64) NOT NULL CHECK (payload_checksum ~ '^[0-9a-f]{64}$'),
    correlation_id uuid NOT NULL,
    causation_id uuid NOT NULL,
    occurred_at timestamptz(6) NOT NULL,
    recorded_at timestamptz(6) NOT NULL,
    created_at timestamptz(6) NOT NULL DEFAULT clock_timestamp()
);

CREATE TABLE IF NOT EXISTS moderation_reports.outbox_deliveries (
    message_id varchar(78) NOT NULL,
    destination varchar(64) NOT NULL,
    status varchar(16) NOT NULL CHECK (status IN ('Pending','Claimed','Retry','Delivered','Quarantined')),
    attempts integer NOT NULL DEFAULT 0 CHECK (attempts >= 0),
    available_at timestamptz(6) NOT NULL DEFAULT clock_timestamp(),
    claim_owner varchar(96) NULL,
    claimed_until timestamptz(6) NULL,
    delivered_at timestamptz(6) NULL,
    last_error_code varchar(96) NULL,
    PRIMARY KEY (message_id, destination),
    CHECK ((status = 'Claimed') = (claim_owner IS NOT NULL AND claimed_until IS NOT NULL))
);

CREATE INDEX IF NOT EXISTS moderation_outbox_claim_idx
    ON moderation_reports.outbox_deliveries(status, available_at, message_id, destination);
