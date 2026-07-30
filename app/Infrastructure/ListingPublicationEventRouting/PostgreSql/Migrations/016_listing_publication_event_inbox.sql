CREATE SCHEMA IF NOT EXISTS listing_lifecycle;

CREATE TABLE IF NOT EXISTS listing_lifecycle.publication_event_inbox (
    inbox_id text PRIMARY KEY CHECK (inbox_id ~ '^lpei:[0-9a-f]{64}$'),
    event_id text NOT NULL UNIQUE CHECK (event_id ~ '^listing-publication-[0-9a-f]{64}$'),
    event_type text NOT NULL,
    payload_version smallint NOT NULL CHECK (payload_version > 0),
    canonical_event text NOT NULL,
    event_checksum char(64) NOT NULL CHECK (event_checksum ~ '^[0-9a-f]{64}$'),
    status text NOT NULL CHECK (status IN ('pending','processing','transferred','rejected')),
    delivery_attempts integer NOT NULL CHECK (delivery_attempts >= 0)
);

CREATE INDEX IF NOT EXISTS publication_event_inbox_pending_lookup
    ON listing_lifecycle.publication_event_inbox (status, inbox_id)
    WHERE status = 'pending';
