CREATE SCHEMA IF NOT EXISTS public_geography;
CREATE TABLE IF NOT EXISTS public_geography.decisions (
    place_id text PRIMARY KEY,
    version integer NOT NULL CHECK (version > 0),
    causation_key text NOT NULL CHECK (length(btrim(causation_key)) > 0),
    revision_checksum char(64) NOT NULL CHECK (revision_checksum ~ '^[0-9a-f]{64}$'),
    payload jsonb NOT NULL,
    payload_checksum char(64) NOT NULL CHECK (payload_checksum ~ '^[0-9a-f]{64}$'),
    updated_at timestamptz(6) NOT NULL DEFAULT clock_timestamp(),
    CHECK (revision_checksum = payload_checksum)
);
