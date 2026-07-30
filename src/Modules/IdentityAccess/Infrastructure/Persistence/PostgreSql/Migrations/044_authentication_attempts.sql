CREATE SCHEMA IF NOT EXISTS identity_access_completion;

CREATE TABLE IF NOT EXISTS identity_access_completion.authentication_attempts (
    attempt_key varchar(128) PRIMARY KEY,
    policy_version varchar(32) NOT NULL,
    failure_count integer NOT NULL DEFAULT 0 CHECK (failure_count >= 0),
    window_started_at timestamptz NULL,
    locked_until timestamptz NULL,
    version bigint NOT NULL CHECK (version >= 1),
    last_intent_id uuid NOT NULL,
    last_intent_checksum char(64) NOT NULL CHECK (last_intent_checksum ~ '^[0-9a-f]{64}$'),
    last_outcome varchar(32) NOT NULL,
    updated_at timestamptz NOT NULL,
    CHECK (locked_until IS NULL OR window_started_at IS NOT NULL)
);
