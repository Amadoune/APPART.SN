CREATE TABLE IF NOT EXISTS listing_lifecycle.listing_creation_intents (
    operation varchar(64) NOT NULL,
    intent_id uuid NOT NULL,
    checksum char(64) NOT NULL,
    listing_id uuid NOT NULL,
    property_id uuid NOT NULL,
    result varchar(16) NOT NULL,
    aggregate_version integer NULL,
    created_at timestamptz(6) NOT NULL,
    CONSTRAINT listing_creation_intents_pk PRIMARY KEY (operation, intent_id),
    CONSTRAINT listing_creation_intents_operation_chk CHECK (operation = 'CreateListingDraftV1'),
    CONSTRAINT listing_creation_intents_checksum_chk CHECK (checksum ~ '^[0-9a-f]{64}$'),
    CONSTRAINT listing_creation_intents_result_chk CHECK (result IN ('pending', 'applied')),
    CONSTRAINT listing_creation_intents_version_chk CHECK (
        (result = 'pending' AND aggregate_version IS NULL)
        OR (result = 'applied' AND aggregate_version IS NOT NULL AND aggregate_version >= 0)
    )
);

CREATE INDEX IF NOT EXISTS listing_creation_intents_listing_idx
    ON listing_lifecycle.listing_creation_intents (listing_id);
