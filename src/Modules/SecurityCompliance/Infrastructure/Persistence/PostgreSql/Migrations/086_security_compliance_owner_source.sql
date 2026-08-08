CREATE SCHEMA IF NOT EXISTS security_compliance;

CREATE TABLE IF NOT EXISTS security_compliance.owner_revision_journal (
    subject_key varchar(255) NOT NULL,
    stream_type varchar(32) NOT NULL CHECK (stream_type IN ('secret_inventory', 'security_audit', 'incident', 'privacy_policy', 'compliance_control')),
    revision bigint NOT NULL CHECK (revision > 0),
    decision varchar(32) NOT NULL CHECK (decision = 'available'),
    effective_at timestamptz NOT NULL,
    recorded_at timestamptz NOT NULL CHECK (recorded_at >= effective_at),
    revision_checksum char(64) NOT NULL CHECK (revision_checksum ~ '^[0-9a-f]{64}$'),
    PRIMARY KEY (subject_key, stream_type, revision)
);

CREATE INDEX IF NOT EXISTS security_compliance_owner_temporal_read
    ON security_compliance.owner_revision_journal (subject_key, stream_type, effective_at DESC, recorded_at DESC, revision DESC);

CREATE TABLE IF NOT EXISTS security_compliance.owner_current_index (
    subject_key varchar(255) NOT NULL,
    stream_type varchar(32) NOT NULL CHECK (stream_type IN ('secret_inventory', 'security_audit', 'incident', 'privacy_policy', 'compliance_control')),
    revision bigint NOT NULL CHECK (revision > 0),
    decision varchar(32) NOT NULL CHECK (decision = 'available'),
    effective_at timestamptz NOT NULL,
    recorded_at timestamptz NOT NULL CHECK (recorded_at >= effective_at),
    revision_checksum char(64) NOT NULL CHECK (revision_checksum ~ '^[0-9a-f]{64}$'),
    PRIMARY KEY (subject_key, stream_type),
    FOREIGN KEY (subject_key, stream_type, revision)
        REFERENCES security_compliance.owner_revision_journal (subject_key, stream_type, revision)
);

REVOKE UPDATE, DELETE, TRUNCATE ON security_compliance.owner_revision_journal FROM PUBLIC;
