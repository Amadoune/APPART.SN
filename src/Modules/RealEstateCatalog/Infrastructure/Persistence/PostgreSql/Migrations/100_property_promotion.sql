CREATE TABLE IF NOT EXISTS real_estate_catalog.property_promotion_commands (
    command_id uuid PRIMARY KEY,
    checksum char(64) NOT NULL,
    property_id uuid NOT NULL UNIQUE,
    owner_account_id uuid NOT NULL,
    authoring_version integer NOT NULL CHECK (authoring_version >= 1),
    occurred_at timestamptz(6) NOT NULL,
    result varchar(32) NOT NULL CHECK (result = 'applied'),
    CONSTRAINT property_promotion_checksum_chk CHECK (checksum ~ '^[0-9a-f]{64}$'),
    CONSTRAINT property_promotion_property_fk FOREIGN KEY (property_id) REFERENCES real_estate_catalog.properties(id) ON DELETE RESTRICT
);
