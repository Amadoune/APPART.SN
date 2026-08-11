CREATE TABLE IF NOT EXISTS listing_lifecycle.publication_command_gateway_ledger (
    command_id uuid PRIMARY KEY,
    listing_id uuid NOT NULL,
    operation varchar(32) NOT NULL CHECK (operation IN ('begin_review','approve_and_publish')),
    command_checksum char(64) NOT NULL CHECK (command_checksum ~ '^[0-9a-f]{64}$'),
    status varchar(32) NOT NULL,
    workflow_version bigint,
    aggregate_version bigint,
    occurred_at timestamptz(6) NOT NULL,
    completed_at timestamptz(6),
    CHECK ((status = 'reserved' AND completed_at IS NULL) OR (status <> 'reserved' AND completed_at IS NOT NULL))
);

CREATE INDEX IF NOT EXISTS listing_publication_gateway_listing_idx
    ON listing_lifecycle.publication_command_gateway_ledger (listing_id, occurred_at, command_id);
