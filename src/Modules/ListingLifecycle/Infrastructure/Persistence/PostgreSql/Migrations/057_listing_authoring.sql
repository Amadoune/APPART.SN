CREATE SCHEMA IF NOT EXISTS listing_authoring;

CREATE TABLE IF NOT EXISTS listing_authoring.drafts (
    listing_id uuid PRIMARY KEY,
    property_id uuid NOT NULL,
    title varchar(160) NOT NULL,
    description text NOT NULL,
    transaction_kind varchar(16) NOT NULL,
    price_minor bigint NULL,
    currency char(3) NULL,
    charges_minor bigint NULL,
    availability_date date NULL,
    contact_preference varchar(16) NOT NULL,
    version integer NOT NULL,
    last_intent_id uuid NOT NULL,
    last_intent_checksum char(64) NOT NULL,
    updated_at timestamptz(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT listing_drafts_transaction_chk CHECK (transaction_kind IN ('sale', 'rent')),
    CONSTRAINT listing_drafts_contact_chk CHECK (contact_preference IN ('platform', 'phone', 'whatsapp')),
    CONSTRAINT listing_drafts_amount_chk CHECK (price_minor IS NULL OR price_minor >= 0),
    CONSTRAINT listing_drafts_charges_chk CHECK (charges_minor IS NULL OR charges_minor >= 0),
    CONSTRAINT listing_drafts_version_chk CHECK (version >= 1),
    CONSTRAINT listing_drafts_checksum_chk CHECK (last_intent_checksum ~ '^[0-9a-f]{64}$')
);

CREATE TABLE IF NOT EXISTS listing_authoring.draft_revisions (
    listing_id uuid NOT NULL,
    version integer NOT NULL,
    intent_id uuid NOT NULL,
    intent_checksum char(64) NOT NULL,
    changed_at timestamptz(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT draft_revisions_pk PRIMARY KEY (listing_id, version),
    CONSTRAINT draft_revisions_intent_uq UNIQUE (listing_id, intent_id),
    CONSTRAINT draft_revisions_version_chk CHECK (version >= 1),
    CONSTRAINT draft_revisions_checksum_chk CHECK (intent_checksum ~ '^[0-9a-f]{64}$')
);

CREATE TABLE IF NOT EXISTS listing_authoring.ownerships (
    listing_id uuid PRIMARY KEY,
    property_id uuid NOT NULL,
    owner_account_id uuid NOT NULL,
    version integer NOT NULL,
    last_intent_id uuid NOT NULL,
    last_intent_checksum char(64) NOT NULL,
    updated_at timestamptz(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT listing_ownerships_version_chk CHECK (version >= 1),
    CONSTRAINT listing_ownerships_checksum_chk CHECK (last_intent_checksum ~ '^[0-9a-f]{64}$')
);

CREATE TABLE IF NOT EXISTS listing_authoring.delegations (
    listing_id uuid NOT NULL,
    delegate_account_id uuid NOT NULL,
    permission varchar(16) NOT NULL,
    CONSTRAINT listing_delegations_pk PRIMARY KEY (listing_id, delegate_account_id, permission),
    CONSTRAINT listing_delegations_permission_chk CHECK (permission IN ('VIEW', 'EDIT', 'SUBMIT'))
);

CREATE TABLE IF NOT EXISTS listing_authoring.portfolio_items (
    account_id uuid NOT NULL,
    listing_id uuid NOT NULL,
    property_id uuid NOT NULL,
    relation varchar(16) NOT NULL,
    draft_version integer NOT NULL,
    ownership_version integer NOT NULL,
    completeness_code varchar(32) NOT NULL,
    source_checkpoint bigint NOT NULL,
    projected_at timestamptz(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT portfolio_items_pk PRIMARY KEY (account_id, listing_id),
    CONSTRAINT portfolio_items_relation_chk CHECK (relation IN ('OWNER', 'DELEGATE')),
    CONSTRAINT portfolio_items_versions_chk CHECK (draft_version >= 0 AND ownership_version >= 1),
    CONSTRAINT portfolio_items_checkpoint_chk CHECK (source_checkpoint >= 0)
);

CREATE INDEX IF NOT EXISTS portfolio_items_account_checkpoint_idx
    ON listing_authoring.portfolio_items (account_id, source_checkpoint, listing_id);
