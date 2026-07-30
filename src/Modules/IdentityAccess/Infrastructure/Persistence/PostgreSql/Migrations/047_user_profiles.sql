CREATE SCHEMA IF NOT EXISTS identity_access_completion;

CREATE TABLE IF NOT EXISTS identity_access_completion.user_profiles (
    account_id uuid PRIMARY KEY,
    display_name_ciphertext text NOT NULL,
    email_ciphertext text NULL,
    email_fingerprint char(64) NULL CHECK (email_fingerprint ~ '^[0-9a-f]{64}$'),
    phone_ciphertext text NULL,
    phone_fingerprint char(64) NULL CHECK (phone_fingerprint ~ '^[0-9a-f]{64}$'),
    normalization_version varchar(32) NOT NULL,
    version bigint NOT NULL CHECK (version >= 1),
    enrolled_at timestamptz NOT NULL,
    updated_at timestamptz NOT NULL,
    last_intent_id uuid NOT NULL,
    last_intent_checksum char(64) NOT NULL CHECK (last_intent_checksum ~ '^[0-9a-f]{64}$')
);
