CREATE SCHEMA IF NOT EXISTS identity_access_completion;

CREATE TABLE IF NOT EXISTS identity_access_completion.identity_claims (
    claim_id uuid PRIMARY KEY,
    account_id uuid NOT NULL,
    claim_type varchar(8) NOT NULL CHECK (claim_type IN ('Email','Phone')),
    claim_ciphertext text NOT NULL,
    claim_fingerprint char(64) NOT NULL CHECK (claim_fingerprint ~ '^[0-9a-f]{64}$'),
    normalization_version varchar(32) NOT NULL,
    state varchar(16) NOT NULL CHECK (state IN ('Reserved','Active','Released','Expired','Superseded')),
    version bigint NOT NULL CHECK (version >= 1),
    reserved_at timestamptz NOT NULL,
    activated_at timestamptz NULL,
    ended_at timestamptz NULL,
    contact_change_id uuid NULL,
    last_intent_id uuid NOT NULL,
    last_intent_checksum char(64) NOT NULL CHECK (last_intent_checksum ~ '^[0-9a-f]{64}$'),
    UNIQUE (claim_type, claim_fingerprint),
    CHECK ((state = 'Active') = (activated_at IS NOT NULL AND ended_at IS NULL)),
    CHECK ((state IN ('Released','Expired','Superseded')) = (ended_at IS NOT NULL))
);
CREATE INDEX IF NOT EXISTS identity_claims_account_state_idx
    ON identity_access_completion.identity_claims (account_id, state);
