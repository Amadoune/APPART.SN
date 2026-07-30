CREATE SCHEMA IF NOT EXISTS real_estate_catalog_authoring;

CREATE TABLE IF NOT EXISTS real_estate_catalog_authoring.property_authoring (
    property_id uuid PRIMARY KEY,
    owner_account_id uuid NOT NULL,
    version integer NOT NULL,
    last_intent_id uuid NOT NULL,
    last_intent_checksum char(64) NOT NULL,
    updated_at timestamptz(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT property_authoring_version_chk CHECK (version >= 1),
    CONSTRAINT property_authoring_checksum_chk CHECK (last_intent_checksum ~ '^[0-9a-f]{64}$')
);
