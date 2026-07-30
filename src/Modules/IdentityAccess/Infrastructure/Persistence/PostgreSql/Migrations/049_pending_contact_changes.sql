CREATE SCHEMA IF NOT EXISTS identity_access_completion;

CREATE TABLE IF NOT EXISTS identity_access_completion.pending_contact_changes (
    change_id uuid PRIMARY KEY,
    account_id uuid NOT NULL,
    contact_type varchar(8) NOT NULL CHECK (contact_type IN ('Email','Phone')),
    target_ciphertext text NOT NULL,
    target_fingerprint char(64) NOT NULL CHECK (target_fingerprint ~ '^[0-9a-f]{64}$'),
    challenge_hash varchar(255) NOT NULL,
    claim_id uuid NOT NULL,
    state varchar(16) NOT NULL CHECK (state IN ('Pending','Verified','Activated','Expired','Cancelled')),
    version bigint NOT NULL CHECK (version >= 1),
    requested_at timestamptz NOT NULL,
    expires_at timestamptz NOT NULL,
    verified_at timestamptz NULL,
    activated_at timestamptz NULL,
    fresh_auth_evidence varchar(128) NOT NULL,
    policy_version varchar(32) NOT NULL,
    normalization_version varchar(32) NOT NULL,
    last_intent_id uuid NOT NULL,
    last_intent_checksum char(64) NOT NULL CHECK (last_intent_checksum ~ '^[0-9a-f]{64}$'),
    CHECK (expires_at > requested_at)
);
CREATE UNIQUE INDEX IF NOT EXISTS one_live_contact_change_per_type
    ON identity_access_completion.pending_contact_changes (account_id, contact_type)
    WHERE state IN ('Pending','Verified');
