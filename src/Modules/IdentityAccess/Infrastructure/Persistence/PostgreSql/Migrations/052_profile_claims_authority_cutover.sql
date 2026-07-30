CREATE SCHEMA IF NOT EXISTS identity_access_completion;

CREATE TABLE IF NOT EXISTS identity_access_completion.profile_claim_seed_runs (
    run_id uuid PRIMARY KEY,
    source_snapshot_version smallint NOT NULL CHECK (source_snapshot_version = 1),
    normalization_version varchar(32) NOT NULL,
    state varchar(16) NOT NULL CHECK (state IN ('Prepared','Quarantined','Committed','RolledBack')),
    source_count integer NOT NULL CHECK (source_count >= 0),
    profile_count integer NOT NULL CHECK (profile_count >= 0),
    claim_count integer NOT NULL CHECK (claim_count >= 0),
    divergence_count integer NOT NULL CHECK (divergence_count >= 0),
    source_checksum char(64) NOT NULL CHECK (source_checksum ~ '^[0-9a-f]{64}$'),
    started_at timestamptz NOT NULL,
    completed_at timestamptz NULL,
    CHECK (state = 'Prepared' OR completed_at IS NOT NULL)
);

CREATE TABLE IF NOT EXISTS identity_access_completion.profile_claim_seed_manifest (
    run_id uuid NOT NULL,
    account_id uuid NOT NULL,
    profile_version bigint NOT NULL CHECK (profile_version >= 1),
    email_claim_id uuid NOT NULL,
    phone_claim_id uuid NOT NULL,
    row_checksum char(64) NOT NULL CHECK (row_checksum ~ '^[0-9a-f]{64}$'),
    PRIMARY KEY (run_id, account_id),
    UNIQUE (run_id, email_claim_id),
    UNIQUE (run_id, phone_claim_id)
);

CREATE TABLE IF NOT EXISTS identity_access_completion.profile_claim_seed_quarantine (
    run_id uuid NOT NULL,
    account_id uuid NOT NULL,
    divergence_code varchar(48) NOT NULL CHECK (divergence_code IN (
        'NormalizationDivergence','DuplicateHistoricalClaim','IncompleteData',
        'ClaimCollision','ProfileConflict','ProtectionFailure'
    )),
    evidence_checksum char(64) NOT NULL CHECK (evidence_checksum ~ '^[0-9a-f]{64}$'),
    detected_at timestamptz NOT NULL,
    resolution_state varchar(16) NOT NULL DEFAULT 'Open' CHECK (resolution_state IN ('Open','Resolved')),
    PRIMARY KEY (run_id, account_id, divergence_code)
);

CREATE TABLE IF NOT EXISTS identity_access_completion.profile_claim_authority (
    authority_key varchar(32) PRIMARY KEY CHECK (authority_key = 'ProfileClaims'),
    authority varchar(16) NOT NULL CHECK (authority IN ('Historical','Profile')),
    generation bigint NOT NULL CHECK (generation >= 0),
    active_run_id uuid NULL,
    changed_at timestamptz NOT NULL,
    last_intent_id uuid NOT NULL,
    last_intent_checksum char(64) NOT NULL CHECK (last_intent_checksum ~ '^[0-9a-f]{64}$'),
    CHECK ((authority = 'Profile') = (active_run_id IS NOT NULL))
);

INSERT INTO identity_access_completion.profile_claim_authority(
    authority_key,authority,generation,active_run_id,changed_at,last_intent_id,last_intent_checksum
) VALUES (
    'ProfileClaims','Historical',0,NULL,TIMESTAMPTZ '1970-01-01 00:00:00+00',
    '00000000-0000-0000-0000-000000000000',repeat('0',64)
) ON CONFLICT (authority_key) DO NOTHING;
