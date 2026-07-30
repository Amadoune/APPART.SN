CREATE TABLE IF NOT EXISTS moderation_reports.atomic_outbox_appends (
    message_id varchar(78) PRIMARY KEY,
    event_id uuid NOT NULL UNIQUE,
    payload_checksum char(64) NOT NULL CHECK (payload_checksum ~ '^[0-9a-f]{64}$'),
    canonical_transport jsonb NOT NULL CHECK (jsonb_typeof(canonical_transport) = 'object'),
    correlation_id uuid NOT NULL,
    causation_id uuid NOT NULL,
    recorded_at timestamptz NOT NULL
);
