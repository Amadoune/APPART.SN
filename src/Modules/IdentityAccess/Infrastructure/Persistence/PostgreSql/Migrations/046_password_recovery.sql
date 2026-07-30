CREATE SCHEMA IF NOT EXISTS identity_access_completion;

CREATE TABLE IF NOT EXISTS identity_access_completion.recovery_challenges (
    challenge_id uuid PRIMARY KEY,
    account_id uuid NOT NULL,
    challenge_hash varchar(255) NOT NULL,
    state varchar(16) NOT NULL CHECK (state IN ('Issued','Consumed','Expired','Revoked')),
    version bigint NOT NULL CHECK (version >= 1),
    issued_at timestamptz NOT NULL,
    expires_at timestamptz NOT NULL,
    consumed_at timestamptz NULL,
    policy_version varchar(32) NOT NULL,
    last_intent_id uuid NOT NULL,
    last_intent_checksum char(64) NOT NULL CHECK (last_intent_checksum ~ '^[0-9a-f]{64}$'),
    CHECK (expires_at > issued_at),
    CHECK ((state = 'Consumed') = (consumed_at IS NOT NULL))
);
CREATE UNIQUE INDEX IF NOT EXISTS recovery_one_issued_per_account
    ON identity_access_completion.recovery_challenges (account_id)
    WHERE state = 'Issued';
