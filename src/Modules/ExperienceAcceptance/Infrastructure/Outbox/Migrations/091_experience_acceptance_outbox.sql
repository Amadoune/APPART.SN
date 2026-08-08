CREATE SCHEMA IF NOT EXISTS experience_acceptance;

CREATE TABLE IF NOT EXISTS experience_acceptance.outbox_message_journal (
    message_id char(64) PRIMARY KEY CHECK (message_id ~ '^[0-9a-f]{64}$'),
    event_id char(64) NOT NULL UNIQUE CHECK (event_id ~ '^[0-9a-f]{64}$'),
    owner_name varchar(64) NOT NULL CHECK (owner_name = 'ExperienceAcceptance'),
    schema_version smallint NOT NULL CHECK (schema_version = 1),
    message_type varchar(96) NOT NULL CHECK (message_type IN (
        'experience-acceptance.responsive-compliance.observed.v1',
        'experience-acceptance.accessibility-compliance.observed.v1',
        'experience-acceptance.user-experience.observed.v1',
        'experience-acceptance.end-to-end-readiness.observed.v1',
        'experience-acceptance.performance-readiness.observed.v1',
        'experience-acceptance.user-acceptance.observed.v1',
        'experience-acceptance.release-candidate.observed.v1'
    )),
    delivery_status varchar(32) NOT NULL CHECK (delivery_status IN (
        'available', 'missing', 'corrupted', 'dependency_unavailable'
    )),
    observed_at timestamptz NOT NULL,
    payload jsonb NOT NULL,
    message_checksum char(64) NOT NULL CHECK (message_checksum ~ '^[0-9a-f]{64}$'),
    created_at timestamptz NOT NULL
);

CREATE TABLE IF NOT EXISTS experience_acceptance.outbox_message_state (
    message_id char(64) PRIMARY KEY REFERENCES experience_acceptance.outbox_message_journal(message_id),
    technical_status varchar(32) NOT NULL CHECK (technical_status IN ('pending', 'claimed', 'retry_scheduled', 'completed', 'attempts_exhausted')),
    attempts smallint NOT NULL DEFAULT 0 CHECK (attempts BETWEEN 0 AND 10),
    available_at timestamptz NOT NULL,
    claimed_at timestamptz NULL,
    completed_at timestamptz NULL
);

CREATE INDEX IF NOT EXISTS experience_acceptance_outbox_eligible
    ON experience_acceptance.outbox_message_state (technical_status, available_at, message_id);

REVOKE UPDATE, DELETE, TRUNCATE ON experience_acceptance.outbox_message_journal FROM PUBLIC;

