CREATE TABLE IF NOT EXISTS media.media_item_lifecycle_transition_contexts (
    media_id uuid NOT NULL,
    version bigint NOT NULL CHECK (version > 1),
    contract_version smallint NOT NULL CHECK (contract_version = 1),
    collection_id uuid NOT NULL,
    collection_version bigint NOT NULL CHECK (collection_version >= 0),
    actor_id uuid NOT NULL,
    occurred_at timestamptz NOT NULL,
    primary_disposition text NOT NULL CHECK (primary_disposition IN ('not_primary','replacement_selected')),
    replacement_media_id uuid NULL,
    context_checksum char(64) NOT NULL CHECK (context_checksum ~ '^[0-9a-f]{64}$'),
    PRIMARY KEY (media_id, version),
    CONSTRAINT media_item_lifecycle_context_replacement_shape CHECK (
        (primary_disposition = 'not_primary' AND replacement_media_id IS NULL)
        OR
        (primary_disposition = 'replacement_selected' AND replacement_media_id IS NOT NULL AND replacement_media_id <> media_id)
    )
);

CREATE INDEX IF NOT EXISTS media_item_lifecycle_context_collection_lookup
    ON media.media_item_lifecycle_transition_contexts (collection_id, collection_version, media_id, version);
