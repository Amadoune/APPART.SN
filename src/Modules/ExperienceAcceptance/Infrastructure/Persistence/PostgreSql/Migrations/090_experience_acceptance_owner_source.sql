CREATE SCHEMA IF NOT EXISTS experience_acceptance;

CREATE TABLE IF NOT EXISTS experience_acceptance.owner_revision_journal (
    scope_key varchar(255) NOT NULL,
    stream_type varchar(32) NOT NULL CHECK (stream_type IN ('responsive_compliance', 'accessibility_compliance', 'user_experience', 'end_to_end_readiness', 'performance_readiness', 'user_acceptance', 'release_candidate')),
    revision bigint NOT NULL CHECK (revision > 0),
    decision varchar(32) NOT NULL CHECK (decision IN ('available', 'missing', 'corrupted', 'dependency_unavailable')),
    effective_at timestamptz NOT NULL,
    recorded_at timestamptz NOT NULL CHECK (recorded_at >= effective_at),
    revision_checksum char(64) NOT NULL CHECK (revision_checksum ~ '^[0-9a-f]{64}$'),
    PRIMARY KEY (scope_key, stream_type, revision)
);

CREATE INDEX IF NOT EXISTS experience_acceptance_owner_temporal_read
    ON experience_acceptance.owner_revision_journal (scope_key, stream_type, effective_at DESC, recorded_at DESC, revision DESC);

CREATE TABLE IF NOT EXISTS experience_acceptance.owner_current_index (
    scope_key varchar(255) NOT NULL,
    stream_type varchar(32) NOT NULL CHECK (stream_type IN ('responsive_compliance', 'accessibility_compliance', 'user_experience', 'end_to_end_readiness', 'performance_readiness', 'user_acceptance', 'release_candidate')),
    revision bigint NOT NULL CHECK (revision > 0),
    decision varchar(32) NOT NULL CHECK (decision IN ('available', 'missing', 'corrupted', 'dependency_unavailable')),
    effective_at timestamptz NOT NULL,
    recorded_at timestamptz NOT NULL CHECK (recorded_at >= effective_at),
    revision_checksum char(64) NOT NULL CHECK (revision_checksum ~ '^[0-9a-f]{64}$'),
    PRIMARY KEY (scope_key, stream_type),
    FOREIGN KEY (scope_key, stream_type, revision)
        REFERENCES experience_acceptance.owner_revision_journal (scope_key, stream_type, revision)
);

REVOKE UPDATE, DELETE, TRUNCATE ON experience_acceptance.owner_revision_journal FROM PUBLIC;

