CREATE SCHEMA IF NOT EXISTS reliability_operations;

CREATE TABLE IF NOT EXISTS reliability_operations.outbox_message_journal (
    message_id char(64) PRIMARY KEY CHECK (message_id ~ '^[0-9a-f]{64}$'),
    event_id char(64) NOT NULL UNIQUE CHECK (event_id ~ '^[0-9a-f]{64}$'),
    owner_name varchar(64) NOT NULL CHECK (owner_name = 'ReliabilityOperations'),
    schema_version smallint NOT NULL CHECK (schema_version = 1),
    message_type varchar(96) NOT NULL CHECK (message_type IN (
        'reliability-operations.observability.observed.v1',
        'reliability-operations.service-health.observed.v1',
        'reliability-operations.alerting.observed.v1',
        'reliability-operations.maintenance-operations.observed.v1',
        'reliability-operations.continuity.observed.v1',
        'reliability-operations.capacity-planning.observed.v1',
        'reliability-operations.operational-readiness.observed.v1'
    )),
    delivery_status varchar(32) NOT NULL CHECK (delivery_status IN (
        'available', 'healthy', 'ready', 'sufficient',
        'degraded', 'at_risk', 'unavailable', 'blocked', 'exhausted',
        'missing', 'corrupted', 'dependency_unavailable'
    )),
    observed_at timestamptz NOT NULL,
    payload jsonb NOT NULL,
    message_checksum char(64) NOT NULL CHECK (message_checksum ~ '^[0-9a-f]{64}$'),
    created_at timestamptz NOT NULL
);

CREATE TABLE IF NOT EXISTS reliability_operations.outbox_message_state (
    message_id char(64) PRIMARY KEY REFERENCES reliability_operations.outbox_message_journal(message_id),
    technical_status varchar(32) NOT NULL CHECK (technical_status IN ('pending', 'claimed', 'retry_scheduled', 'completed', 'attempts_exhausted')),
    attempts smallint NOT NULL DEFAULT 0 CHECK (attempts BETWEEN 0 AND 10),
    available_at timestamptz NOT NULL,
    claimed_at timestamptz NULL,
    completed_at timestamptz NULL
);

CREATE INDEX IF NOT EXISTS reliability_operations_outbox_eligible
    ON reliability_operations.outbox_message_state (technical_status, available_at, message_id);

REVOKE UPDATE, DELETE, TRUNCATE ON reliability_operations.outbox_message_journal FROM PUBLIC;

