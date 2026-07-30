CREATE SCHEMA IF NOT EXISTS professional_profile;

CREATE TABLE IF NOT EXISTS professional_profile.public_profiles (
    professional_id uuid PRIMARY KEY,
    visibility varchar(16) NOT NULL CHECK (visibility IN ('draft','visible','hidden')),
    public_name varchar(160) NOT NULL,
    description text NOT NULL,
    categories jsonb NOT NULL CHECK (jsonb_typeof(categories) = 'array'),
    languages jsonb NOT NULL CHECK (jsonb_typeof(languages) = 'array'),
    public_contacts jsonb NOT NULL CHECK (jsonb_typeof(public_contacts) = 'object'),
    media_references jsonb NOT NULL CHECK (jsonb_typeof(media_references) = 'array'),
    revision integer NOT NULL CHECK (revision > 0),
    version integer NOT NULL CHECK (version > 0),
    policy_version varchar(64) NOT NULL,
    last_intent_id uuid NOT NULL,
    last_intent_checksum char(64) NOT NULL CHECK (last_intent_checksum ~ '^[0-9a-f]{64}$'),
    updated_at timestamptz(6) NOT NULL
);

CREATE TABLE IF NOT EXISTS professional_profile.public_profile_revisions (
    professional_id uuid NOT NULL,
    revision integer NOT NULL CHECK (revision > 0),
    version integer NOT NULL CHECK (version > 0),
    snapshot jsonb NOT NULL CHECK (jsonb_typeof(snapshot) = 'object'),
    snapshot_checksum char(64) NOT NULL CHECK (snapshot_checksum ~ '^[0-9a-f]{64}$'),
    recorded_at timestamptz(6) NOT NULL,
    PRIMARY KEY (professional_id, revision)
);

CREATE TABLE IF NOT EXISTS professional_profile.public_profile_intents (
    professional_id uuid NOT NULL,
    intent_id uuid NOT NULL,
    intent_checksum char(64) NOT NULL CHECK (intent_checksum ~ '^[0-9a-f]{64}$'),
    recorded_at timestamptz(6) NOT NULL,
    PRIMARY KEY (professional_id, intent_id)
);

CREATE TABLE IF NOT EXISTS professional_profile.verifications (
    professional_id uuid PRIMARY KEY,
    disposition varchar(16) NOT NULL CHECK (disposition IN ('unverified','pending','verified','rejected','expired','revoked')),
    evidence_references jsonb NOT NULL CHECK (jsonb_typeof(evidence_references) = 'array'),
    policy_version varchar(64) NOT NULL,
    decision_authority_id uuid NULL,
    expires_at timestamptz(6) NULL,
    decision_sequence integer NOT NULL CHECK (decision_sequence >= 0),
    version integer NOT NULL CHECK (version > 0),
    last_intent_id uuid NOT NULL,
    last_intent_checksum char(64) NOT NULL CHECK (last_intent_checksum ~ '^[0-9a-f]{64}$'),
    updated_at timestamptz(6) NOT NULL
);

CREATE TABLE IF NOT EXISTS professional_profile.verification_decisions (
    professional_id uuid NOT NULL,
    decision_sequence integer NOT NULL CHECK (decision_sequence >= 0),
    disposition varchar(16) NOT NULL,
    policy_version varchar(64) NOT NULL,
    decision_authority_id uuid NULL,
    evidence_references jsonb NOT NULL CHECK (jsonb_typeof(evidence_references) = 'array'),
    decided_at timestamptz(6) NOT NULL,
    PRIMARY KEY (professional_id, decision_sequence)
);

CREATE TABLE IF NOT EXISTS professional_profile.verification_intents (
    professional_id uuid NOT NULL,
    intent_id uuid NOT NULL,
    intent_checksum char(64) NOT NULL CHECK (intent_checksum ~ '^[0-9a-f]{64}$'),
    recorded_at timestamptz(6) NOT NULL,
    PRIMARY KEY (professional_id, intent_id)
);

CREATE TABLE IF NOT EXISTS professional_profile.public_portfolios (
    professional_id uuid PRIMARY KEY,
    listing_ids jsonb NOT NULL CHECK (jsonb_typeof(listing_ids) = 'array'),
    checkpoint bigint NOT NULL CHECK (checkpoint >= 0),
    version integer NOT NULL CHECK (version > 0),
    last_intent_id uuid NOT NULL,
    last_intent_checksum char(64) NOT NULL CHECK (last_intent_checksum ~ '^[0-9a-f]{64}$'),
    updated_at timestamptz(6) NOT NULL
);

CREATE TABLE IF NOT EXISTS professional_profile.public_portfolio_intents (
    professional_id uuid NOT NULL,
    intent_id uuid NOT NULL,
    intent_checksum char(64) NOT NULL CHECK (intent_checksum ~ '^[0-9a-f]{64}$'),
    recorded_at timestamptz(6) NOT NULL,
    PRIMARY KEY (professional_id, intent_id)
);
