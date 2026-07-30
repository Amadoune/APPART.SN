CREATE SCHEMA IF NOT EXISTS geography;

CREATE TABLE IF NOT EXISTS geography.place_lifecycle_transitions (
    place_id uuid NOT NULL,
    version bigint NOT NULL CHECK (version > 0),
    entry_kind text NOT NULL CHECK (entry_kind IN ('enrollment','transition')),
    previous_state text NULL,
    current_state text NOT NULL CHECK (current_state IN ('enabled','disabled','merged')),
    action text NULL,
    target_id uuid NULL,
    target_version bigint NULL CHECK (target_version > 0),
    target_state text NULL CHECK (target_state IN ('enabled','disabled','merged')),
    source_type text NULL,
    target_type text NULL,
    source_country char(2) NULL,
    target_country char(2) NULL,
    actor_id uuid NULL,
    occurred_at timestamptz NULL,
    intent_id uuid NULL,
    context_version smallint NULL,
    entry_checksum char(64) NOT NULL CHECK (entry_checksum ~ '^[0-9a-f]{64}$'),
    PRIMARY KEY (place_id, version),
    CONSTRAINT place_lifecycle_entry_shape CHECK (
        (
            entry_kind = 'enrollment'
            AND previous_state IS NULL
            AND action IS NULL
            AND target_id IS NULL
            AND target_version IS NULL
            AND target_state IS NULL
            AND source_type IS NULL
            AND target_type IS NULL
            AND source_country IS NULL
            AND target_country IS NULL
            AND actor_id IS NULL
            AND occurred_at IS NULL
            AND intent_id IS NULL
            AND context_version IS NULL
        )
        OR
        (
            entry_kind = 'transition'
            AND previous_state IS NOT NULL
            AND action IS NOT NULL
            AND target_id IS NOT NULL
            AND target_version IS NOT NULL
            AND target_state IS NOT NULL
            AND source_type IS NOT NULL
            AND target_type IS NOT NULL
            AND source_country IS NOT NULL
            AND target_country IS NOT NULL
            AND actor_id IS NOT NULL
            AND occurred_at IS NOT NULL
            AND intent_id IS NOT NULL
            AND context_version = 1
        )
    ),
    CONSTRAINT place_lifecycle_allowed_transition CHECK (
        entry_kind = 'enrollment'
        OR (previous_state, action, current_state) IN (
            ('enabled','disable','disabled'),
            ('disabled','enable','enabled'),
            ('enabled','merge','merged'),
            ('disabled','merge','merged')
        )
    )
);

CREATE UNIQUE INDEX IF NOT EXISTS place_lifecycle_intent_uniqueness
    ON geography.place_lifecycle_transitions (place_id, intent_id)
    WHERE intent_id IS NOT NULL;

CREATE INDEX IF NOT EXISTS place_lifecycle_current_lookup
    ON geography.place_lifecycle_transitions (place_id, version DESC)
    INCLUDE (current_state, entry_checksum);
