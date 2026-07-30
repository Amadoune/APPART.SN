CREATE SCHEMA IF NOT EXISTS media;

CREATE TABLE IF NOT EXISTS media.media_attachment_intents (
    operation varchar(64) NOT NULL CHECK (operation = 'AttachReadyMediaAssetV1'),
    intent_id uuid NOT NULL,
    checksum char(64) NOT NULL CHECK (checksum ~ '^[0-9a-f]{64}$'),
    collection_id uuid NOT NULL,
    property_id uuid NOT NULL,
    media_id uuid NOT NULL,
    result varchar(16) NOT NULL CHECK (result IN ('pending','applied')),
    aggregate_version integer NULL,
    created_at timestamptz(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT media_attachment_intents_pk PRIMARY KEY(operation,intent_id),
    CONSTRAINT media_attachment_intents_result_chk CHECK (
        (result='pending' AND aggregate_version IS NULL)
        OR (result='applied' AND aggregate_version IS NOT NULL AND aggregate_version >= 1)
    )
);

CREATE INDEX IF NOT EXISTS media_attachment_intents_collection_idx
    ON media.media_attachment_intents(collection_id, media_id);
