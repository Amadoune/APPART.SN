CREATE SCHEMA IF NOT EXISTS identity_access_completion;

CREATE TABLE IF NOT EXISTS identity_access_completion.session_invalidation_checkpoints (
    account_id uuid PRIMARY KEY,
    checkpoint bigint NOT NULL CHECK (checkpoint >= 0),
    version bigint NOT NULL CHECK (version >= 1),
    last_intent_id uuid NOT NULL,
    last_intent_checksum char(64) NOT NULL CHECK (last_intent_checksum ~ '^[0-9a-f]{64}$'),
    updated_at timestamptz NOT NULL
);

CREATE TABLE IF NOT EXISTS identity_access_completion.sessions (
    session_id uuid PRIMARY KEY,
    account_id uuid NOT NULL,
    secret_hash varchar(255) NOT NULL,
    state varchar(16) NOT NULL CHECK (state IN ('Active','Rotated','Revoked','Expired')),
    version bigint NOT NULL CHECK (version >= 1),
    issued_at timestamptz NOT NULL,
    expires_at timestamptz NOT NULL,
    last_seen_at timestamptz NOT NULL,
    rotated_to uuid NULL,
    device_reference varchar(128) NULL,
    issued_checkpoint bigint NOT NULL CHECK (issued_checkpoint >= 0),
    last_intent_id uuid NOT NULL,
    last_intent_checksum char(64) NOT NULL CHECK (last_intent_checksum ~ '^[0-9a-f]{64}$'),
    CHECK (expires_at > issued_at),
    CHECK ((state = 'Rotated') = (rotated_to IS NOT NULL))
);
CREATE INDEX IF NOT EXISTS sessions_account_state_idx
    ON identity_access_completion.sessions (account_id, state);
