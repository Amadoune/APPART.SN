CREATE SCHEMA IF NOT EXISTS reservation_lifecycle;

CREATE TABLE IF NOT EXISTS reservation_lifecycle.reservation_lifecycle_event_inbox (
    inbox_id text PRIMARY KEY CHECK (inbox_id ~ '^rlei:[0-9a-f]{64}$'),
    message_id text NOT NULL UNIQUE CHECK (message_id ~ '^reservation-lifecycle-delivery-[0-9a-f]{64}$'),
    message_type text NOT NULL,
    transport_version smallint NOT NULL CHECK (transport_version = 1),
    canonical_event text NOT NULL,
    source text NOT NULL CHECK (source = 'ReservationLifecycle'),
    business_event_id text NOT NULL CHECK (business_event_id ~ '^reservation-lifecycle-[0-9a-f]{64}$'),
    payload_checksum char(64) NOT NULL CHECK (payload_checksum ~ '^[0-9a-f]{64}$'),
    transport_envelope text NOT NULL
);

CREATE INDEX IF NOT EXISTS reservation_lifecycle_event_inbox_restore_lookup
    ON reservation_lifecycle.reservation_lifecycle_event_inbox (message_type, transport_version, message_id);
