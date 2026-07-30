CREATE SCHEMA IF NOT EXISTS identity_access;

CREATE TABLE IF NOT EXISTS identity_access.accounts (
    account_id uuid PRIMARY KEY,
    email varchar(254) NOT NULL,
    phone varchar(16) NOT NULL,
    person_name varchar(120) NOT NULL,
    last_changed_at timestamptz NOT NULL,
    last_changed_at_offset smallint NOT NULL CHECK (last_changed_at_offset BETWEEN -840 AND 840),
    historical_suspended boolean NOT NULL,
    historical_version bigint NOT NULL CHECK (historical_version >= 0),
    snapshot_version smallint NOT NULL CHECK (snapshot_version = 1),
    CONSTRAINT historical_accounts_email_uq UNIQUE (email),
    CONSTRAINT historical_accounts_phone_uq UNIQUE (phone)
);

CREATE TABLE IF NOT EXISTS identity_access.account_credentials (
    account_id uuid PRIMARY KEY REFERENCES identity_access.accounts(account_id) ON DELETE CASCADE,
    encoded_password_hash varchar(255) NOT NULL,
    changed_at timestamptz NOT NULL,
    changed_at_offset smallint NOT NULL CHECK (changed_at_offset BETWEEN -840 AND 840)
);

CREATE TABLE IF NOT EXISTS identity_access.account_verifications (
    account_id uuid NOT NULL REFERENCES identity_access.accounts(account_id) ON DELETE CASCADE,
    channel varchar(8) NOT NULL CHECK (channel IN ('email','phone')),
    verification_token varchar(128) NOT NULL,
    issued_at timestamptz NOT NULL,
    issued_at_offset smallint NOT NULL CHECK (issued_at_offset BETWEEN -840 AND 840),
    expires_at timestamptz NOT NULL,
    expires_at_offset smallint NOT NULL CHECK (expires_at_offset BETWEEN -840 AND 840),
    verified_at timestamptz NULL,
    verified_at_offset smallint NULL CHECK (verified_at_offset BETWEEN -840 AND 840),
    PRIMARY KEY (account_id, channel),
    CONSTRAINT historical_account_verification_chronology CHECK (
        expires_at > issued_at
        AND (verified_at IS NULL OR (verified_at >= issued_at AND verified_at < expires_at))
        AND ((verified_at IS NULL) = (verified_at_offset IS NULL))
    )
);

CREATE TABLE IF NOT EXISTS identity_access.account_role_assignments (
    account_id uuid NOT NULL REFERENCES identity_access.accounts(account_id) ON DELETE CASCADE,
    ordinal integer NOT NULL CHECK (ordinal >= 0),
    role_id varchar(64) NOT NULL,
    granted_at timestamptz NOT NULL,
    granted_at_offset smallint NOT NULL CHECK (granted_at_offset BETWEEN -840 AND 840),
    revoked_at timestamptz NULL,
    revoked_at_offset smallint NULL CHECK (revoked_at_offset BETWEEN -840 AND 840),
    PRIMARY KEY (account_id, ordinal),
    CONSTRAINT historical_account_role_chronology CHECK (
        (revoked_at IS NULL AND revoked_at_offset IS NULL)
        OR (revoked_at >= granted_at AND revoked_at_offset IS NOT NULL)
    )
);

CREATE UNIQUE INDEX IF NOT EXISTS historical_account_one_active_role
    ON identity_access.account_role_assignments(account_id, role_id)
    WHERE revoked_at IS NULL;

CREATE TABLE IF NOT EXISTS identity_access.account_consents (
    account_id uuid NOT NULL REFERENCES identity_access.accounts(account_id) ON DELETE CASCADE,
    ordinal integer NOT NULL CHECK (ordinal >= 0),
    purpose varchar(64) NOT NULL,
    granted_at timestamptz NOT NULL,
    granted_at_offset smallint NOT NULL CHECK (granted_at_offset BETWEEN -840 AND 840),
    withdrawn_at timestamptz NULL,
    withdrawn_at_offset smallint NULL CHECK (withdrawn_at_offset BETWEEN -840 AND 840),
    PRIMARY KEY (account_id, ordinal),
    CONSTRAINT historical_account_consent_chronology CHECK (
        (withdrawn_at IS NULL AND withdrawn_at_offset IS NULL)
        OR (withdrawn_at >= granted_at AND withdrawn_at_offset IS NOT NULL)
    )
);

CREATE UNIQUE INDEX IF NOT EXISTS historical_account_one_active_consent
    ON identity_access.account_consents(account_id, purpose)
    WHERE withdrawn_at IS NULL;
