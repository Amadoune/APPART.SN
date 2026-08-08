CREATE SCHEMA IF NOT EXISTS search_discovery;

CREATE TABLE IF NOT EXISTS search_discovery.search_owner_revision_journal (
    document_id uuid NOT NULL,
    revision bigint NOT NULL CHECK (revision > 0),
    decision_id uuid NOT NULL,
    listing_id uuid NOT NULL,
    state varchar(16) NOT NULL CHECK (state IN ('visible','hidden','removed')),
    payload jsonb NOT NULL,
    payload_checksum char(64) NOT NULL CHECK (payload_checksum ~ '^[0-9a-f]{64}$'),
    effective_at timestamptz(6) NOT NULL,
    recorded_at timestamptz(6) NOT NULL,
    revision_checksum char(64) NOT NULL CHECK (revision_checksum ~ '^[0-9a-f]{64}$'),
    PRIMARY KEY (document_id, revision),
    UNIQUE (decision_id),
    UNIQUE (document_id, effective_at),
    CHECK (recorded_at >= effective_at)
);

CREATE TABLE IF NOT EXISTS search_discovery.search_owner_current_index (
    document_id uuid PRIMARY KEY,
    revision bigint NOT NULL CHECK (revision > 0),
    decision_id uuid NOT NULL UNIQUE,
    listing_id uuid NOT NULL,
    state varchar(16) NOT NULL CHECK (state IN ('visible','hidden','removed')),
    payload jsonb NOT NULL,
    payload_checksum char(64) NOT NULL CHECK (payload_checksum ~ '^[0-9a-f]{64}$'),
    effective_at timestamptz(6) NOT NULL,
    recorded_at timestamptz(6) NOT NULL,
    revision_checksum char(64) NOT NULL CHECK (revision_checksum ~ '^[0-9a-f]{64}$')
);

CREATE INDEX IF NOT EXISTS search_owner_temporal_read
    ON search_discovery.search_owner_revision_journal
    (document_id, effective_at DESC, recorded_at DESC, revision DESC);

CREATE INDEX IF NOT EXISTS search_owner_current_visibility
    ON search_discovery.search_owner_current_index
    (state, revision DESC, document_id);
