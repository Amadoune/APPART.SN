CREATE SCHEMA IF NOT EXISTS search_discovery;

CREATE TABLE IF NOT EXISTS search_discovery.search_query_resolution_revision_journal (
    query_fingerprint char(64) NOT NULL CHECK (query_fingerprint ~ '^[0-9a-f]{64}$'),
    revision bigint NOT NULL CHECK (revision > 0),
    decision varchar(16) NOT NULL CHECK (decision IN ('found','empty')),
    effective_at timestamptz(6) NOT NULL,
    recorded_at timestamptz(6) NOT NULL,
    revision_checksum char(64) NOT NULL CHECK (revision_checksum ~ '^[0-9a-f]{64}$'),
    PRIMARY KEY (query_fingerprint, revision),
    UNIQUE (query_fingerprint, effective_at),
    CHECK (recorded_at >= effective_at)
);

CREATE TABLE IF NOT EXISTS search_discovery.search_query_resolution_current_index (
    query_fingerprint char(64) PRIMARY KEY CHECK (query_fingerprint ~ '^[0-9a-f]{64}$'),
    revision bigint NOT NULL CHECK (revision > 0),
    decision varchar(16) NOT NULL CHECK (decision IN ('found','empty')),
    effective_at timestamptz(6) NOT NULL,
    recorded_at timestamptz(6) NOT NULL,
    revision_checksum char(64) NOT NULL CHECK (revision_checksum ~ '^[0-9a-f]{64}$')
);

CREATE INDEX IF NOT EXISTS search_query_resolution_temporal_read
    ON search_discovery.search_query_resolution_revision_journal
    (query_fingerprint, effective_at DESC, recorded_at DESC, revision DESC);
