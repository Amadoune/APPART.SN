CREATE SCHEMA IF NOT EXISTS media;

CREATE TABLE IF NOT EXISTS media.media_item_lifecycle_transitions (
    media_id uuid NOT NULL,
    version bigint NOT NULL CHECK (version > 0),
    previous_state text NULL,
    current_state text NOT NULL CHECK (current_state IN ('active','removed','archived')),
    action text NULL,
    transition_checksum char(64) NOT NULL CHECK (transition_checksum ~ '^[0-9a-f]{64}$'),
    PRIMARY KEY (media_id, version),
    CONSTRAINT media_item_lifecycle_shape CHECK (
        (version = 1 AND previous_state IS NULL AND current_state = 'active' AND action IS NULL)
        OR
        (version > 1 AND previous_state IS NOT NULL AND action IS NOT NULL)
    ),
    CONSTRAINT media_item_lifecycle_allowed_transition CHECK (
        version = 1
        OR (previous_state, action, current_state) IN (
            ('active','remove','removed'),
            ('active','archive','archived')
        )
    )
);

CREATE INDEX IF NOT EXISTS media_item_lifecycle_current_lookup
    ON media.media_item_lifecycle_transitions (media_id, version DESC)
    INCLUDE (current_state, transition_checksum);
