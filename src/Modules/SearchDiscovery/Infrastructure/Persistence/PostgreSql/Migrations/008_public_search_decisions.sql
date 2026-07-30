CREATE SCHEMA IF NOT EXISTS search_discovery;

CREATE TABLE IF NOT EXISTS search_discovery.public_search_decisions (
    listing_id uuid PRIMARY KEY,
    decision_id uuid NOT NULL UNIQUE,
    version integer NOT NULL CHECK (version > 0),
    state varchar(16) NOT NULL CHECK (state IN ('visible','hidden','removed')),
    payload jsonb NOT NULL,
    payload_checksum char(64) NOT NULL CHECK (payload_checksum ~ '^[0-9a-f]{64}$'),
    updated_at timestamptz(6) NOT NULL DEFAULT clock_timestamp()
);
