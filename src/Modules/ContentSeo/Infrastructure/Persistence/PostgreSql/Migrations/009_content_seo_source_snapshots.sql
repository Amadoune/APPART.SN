CREATE SCHEMA IF NOT EXISTS content_seo;

CREATE TABLE IF NOT EXISTS content_seo.public_source_snapshots (
    listing_id uuid PRIMARY KEY,
    snapshot_id uuid NOT NULL UNIQUE,
    version integer NOT NULL CHECK (version > 0),
    payload jsonb NOT NULL,
    payload_checksum char(64) NOT NULL CHECK (payload_checksum ~ '^[0-9a-f]{64}$'),
    updated_at timestamptz(6) NOT NULL DEFAULT clock_timestamp()
);
