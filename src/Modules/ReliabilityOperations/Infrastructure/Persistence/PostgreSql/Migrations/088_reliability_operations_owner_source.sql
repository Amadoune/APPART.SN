CREATE SCHEMA IF NOT EXISTS reliability_operations;

CREATE TABLE IF NOT EXISTS reliability_operations.owner_revision_journal (
    scope_key varchar(255) NOT NULL,
    stream_type varchar(32) NOT NULL CHECK (stream_type IN ('observability', 'service_health', 'alerting', 'continuity', 'maintenance_operations', 'capacity_planning', 'operational_readiness')),
    revision bigint NOT NULL CHECK (revision > 0),
    decision varchar(32) NOT NULL CHECK (
        (stream_type = 'observability' AND decision IN ('available', 'degraded', 'missing', 'dependency_unavailable')) OR
        (stream_type = 'service_health' AND decision IN ('healthy', 'degraded', 'unavailable', 'dependency_unavailable')) OR
        (stream_type = 'alerting' AND decision IN ('ready', 'degraded', 'unavailable', 'dependency_unavailable')) OR
        (stream_type = 'continuity' AND decision IN ('ready', 'at_risk', 'blocked', 'dependency_unavailable')) OR
        (stream_type = 'maintenance_operations' AND decision IN ('ready', 'degraded', 'blocked', 'dependency_unavailable')) OR
        (stream_type = 'capacity_planning' AND decision IN ('sufficient', 'at_risk', 'exhausted', 'dependency_unavailable')) OR
        (stream_type = 'operational_readiness' AND decision IN ('ready', 'at_risk', 'blocked', 'dependency_unavailable'))
    ),
    effective_at timestamptz NOT NULL,
    recorded_at timestamptz NOT NULL CHECK (recorded_at >= effective_at),
    revision_checksum char(64) NOT NULL CHECK (revision_checksum ~ '^[0-9a-f]{64}$'),
    PRIMARY KEY (scope_key, stream_type, revision)
);

CREATE INDEX IF NOT EXISTS reliability_operations_owner_temporal_read
    ON reliability_operations.owner_revision_journal (scope_key, stream_type, effective_at DESC, recorded_at DESC, revision DESC);

CREATE TABLE IF NOT EXISTS reliability_operations.owner_current_index (
    scope_key varchar(255) NOT NULL,
    stream_type varchar(32) NOT NULL CHECK (stream_type IN ('observability', 'service_health', 'alerting', 'continuity', 'maintenance_operations', 'capacity_planning', 'operational_readiness')),
    revision bigint NOT NULL CHECK (revision > 0),
    decision varchar(32) NOT NULL CHECK (
        (stream_type = 'observability' AND decision IN ('available', 'degraded', 'missing', 'dependency_unavailable')) OR
        (stream_type = 'service_health' AND decision IN ('healthy', 'degraded', 'unavailable', 'dependency_unavailable')) OR
        (stream_type = 'alerting' AND decision IN ('ready', 'degraded', 'unavailable', 'dependency_unavailable')) OR
        (stream_type = 'continuity' AND decision IN ('ready', 'at_risk', 'blocked', 'dependency_unavailable')) OR
        (stream_type = 'maintenance_operations' AND decision IN ('ready', 'degraded', 'blocked', 'dependency_unavailable')) OR
        (stream_type = 'capacity_planning' AND decision IN ('sufficient', 'at_risk', 'exhausted', 'dependency_unavailable')) OR
        (stream_type = 'operational_readiness' AND decision IN ('ready', 'at_risk', 'blocked', 'dependency_unavailable'))
    ),
    effective_at timestamptz NOT NULL,
    recorded_at timestamptz NOT NULL CHECK (recorded_at >= effective_at),
    revision_checksum char(64) NOT NULL CHECK (revision_checksum ~ '^[0-9a-f]{64}$'),
    PRIMARY KEY (scope_key, stream_type),
    FOREIGN KEY (scope_key, stream_type, revision)
        REFERENCES reliability_operations.owner_revision_journal (scope_key, stream_type, revision)
);

REVOKE UPDATE, DELETE, TRUNCATE ON reliability_operations.owner_revision_journal FROM PUBLIC;
