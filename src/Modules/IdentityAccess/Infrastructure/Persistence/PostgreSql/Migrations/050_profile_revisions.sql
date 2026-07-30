CREATE SCHEMA IF NOT EXISTS identity_access_completion;

CREATE TABLE IF NOT EXISTS identity_access_completion.profile_revisions (
    revision_id uuid PRIMARY KEY,
    account_id uuid NOT NULL,
    profile_version bigint NOT NULL CHECK (profile_version >= 1),
    revision_type varchar(32) NOT NULL CHECK (revision_type IN ('ProfileEnrolled','DisplayNameChanged','EmailChanged','PhoneChanged')),
    actor_id uuid NOT NULL,
    occurred_at timestamptz NOT NULL,
    source_intent_id uuid NOT NULL,
    source_change_id uuid NULL,
    old_value_ciphertext text NULL,
    new_value_ciphertext text NULL,
    policy_version varchar(32) NOT NULL,
    normalization_version varchar(32) NOT NULL,
    revision_checksum char(64) NOT NULL CHECK (revision_checksum ~ '^[0-9a-f]{64}$'),
    UNIQUE (account_id, profile_version),
    UNIQUE (account_id, source_intent_id)
);
