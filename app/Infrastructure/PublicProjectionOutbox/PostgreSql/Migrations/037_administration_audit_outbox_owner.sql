CREATE SCHEMA IF NOT EXISTS administration_audit;

CREATE TABLE IF NOT EXISTS administration_audit.public_projection_outbox_messages (
    message_id text PRIMARY KEY,
    idempotency_key text NOT NULL UNIQUE,
    source_module varchar(64) NOT NULL,
    aggregate_type varchar(64) NOT NULL,
    aggregate_id varchar(255) NOT NULL,
    aggregate_version integer NOT NULL CHECK (aggregate_version > 0),
    event_index integer NOT NULL CHECK (event_index > 0),
    event_type varchar(128) NOT NULL,
    payload_version integer NOT NULL CHECK (payload_version > 0),
    payload jsonb NOT NULL CHECK (jsonb_typeof(payload) = 'object'),
    payload_checksum char(64) NOT NULL,
    occurred_at timestamptz(6) NOT NULL,
    recorded_at timestamptz(6) NOT NULL,
    correlation_id varchar(255) NULL,
    causation_id varchar(255) NULL,
    created_at timestamptz(6) NOT NULL DEFAULT clock_timestamp(),
    UNIQUE (aggregate_type, aggregate_id, aggregate_version, event_index, event_type, payload_version)
);

CREATE TABLE IF NOT EXISTS administration_audit.public_projection_outbox_deliveries (
    message_id text NOT NULL REFERENCES administration_audit.public_projection_outbox_messages(message_id) ON DELETE RESTRICT,
    consumer_id varchar(128) NOT NULL,
    status varchar(48) NOT NULL CHECK (status IN ('pending','claimed','retry_scheduled','blocked_by_sequence_gap','blocked_by_source_readiness','delivered','quarantined')),
    attempts integer NOT NULL DEFAULT 0 CHECK (attempts >= 0),
    claim_state varchar(32) NOT NULL CHECK (claim_state IN ('unclaimed','claimed','released','expired','abandoned')),
    claim_owner_id varchar(255) NULL,
    claimed_at timestamptz(6) NULL,
    claimed_until timestamptz(6) NULL,
    retry_classification varchar(32) NULL,
    retry_delay_seconds integer NULL CHECK (retry_delay_seconds IS NULL OR retry_delay_seconds >= 0),
    retry_allowed boolean NULL,
    available_at timestamptz(6) NULL,
    delivered_at timestamptz(6) NULL,
    quarantine_reason varchar(48) NULL,
    last_error_code varchar(255) NULL,
    PRIMARY KEY (message_id, consumer_id),
    CHECK ((status = 'claimed') = (claim_state = 'claimed' AND claim_owner_id IS NOT NULL AND claimed_at IS NOT NULL AND claimed_until IS NOT NULL)),
    CHECK ((status = 'quarantined') = (quarantine_reason IS NOT NULL)),
    CHECK ((status = 'retry_scheduled') = (retry_classification IS NOT NULL AND retry_delay_seconds IS NOT NULL AND retry_allowed IS NOT NULL))
);

CREATE INDEX IF NOT EXISTS public_projection_outbox_claim_idx
    ON administration_audit.public_projection_outbox_deliveries (consumer_id, status, available_at, message_id);

CREATE INDEX IF NOT EXISTS public_projection_outbox_order_idx
    ON administration_audit.public_projection_outbox_messages (aggregate_type, aggregate_id, aggregate_version, event_index);

CREATE TABLE IF NOT EXISTS administration_audit.public_projection_outbox_cursors (
    consumer_id varchar(128) NOT NULL,
    source_module varchar(64) NOT NULL,
    aggregate_type varchar(64) NOT NULL,
    aggregate_id varchar(255) NOT NULL,
    progress_version integer NULL,
    progress_index integer NULL,
    high_watermark_version integer NULL,
    high_watermark_index integer NULL,
    last_message_id text NULL,
    PRIMARY KEY (consumer_id, source_module, aggregate_type, aggregate_id),
    CHECK ((progress_version IS NULL) = (progress_index IS NULL)),
    CHECK ((high_watermark_version IS NULL) = (high_watermark_index IS NULL))
);

CREATE TABLE IF NOT EXISTS administration_audit.public_projection_outbox_replays (
    consumer_id varchar(128) NOT NULL,
    source_module varchar(64) NOT NULL,
    aggregate_type varchar(64) NOT NULL,
    aggregate_id varchar(255) NOT NULL,
    from_version integer NULL,
    from_index integer NULL,
    to_version integer NOT NULL,
    to_index integer NOT NULL,
    requested_at timestamptz(6) NOT NULL DEFAULT clock_timestamp(),
    CHECK ((from_version IS NULL) = (from_index IS NULL))
);
