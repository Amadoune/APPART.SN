CREATE SCHEMA IF NOT EXISTS reservation_lifecycle;

CREATE TABLE IF NOT EXISTS reservation_lifecycle.reservation_lifecycle_transitions (
    reservation_id uuid NOT NULL,
    version bigint NOT NULL CHECK (version > 0),
    previous_state text NULL,
    current_state text NOT NULL CHECK (current_state IN ('draft','requested','confirmed','in_progress','completed','cancelled','expired','rejected')),
    action text NULL,
    transition_checksum char(64) NOT NULL CHECK (transition_checksum ~ '^[0-9a-f]{64}$'),
    PRIMARY KEY (reservation_id, version),
    CONSTRAINT reservation_lifecycle_shape CHECK (
        (version = 1 AND previous_state IS NULL AND action IS NULL)
        OR
        (version > 1 AND previous_state IS NOT NULL AND action IS NOT NULL)
    ),
    CONSTRAINT reservation_lifecycle_allowed_transition CHECK (
        version = 1
        OR (previous_state, action, current_state) IN (
            ('draft','submit','requested'),
            ('draft','cancel','cancelled'),
            ('requested','confirm','confirmed'),
            ('requested','reject','rejected'),
            ('requested','cancel','cancelled'),
            ('requested','expire','expired'),
            ('confirmed','start','in_progress'),
            ('confirmed','cancel','cancelled'),
            ('confirmed','expire','expired'),
            ('in_progress','complete','completed'),
            ('in_progress','cancel','cancelled')
        )
    )
);

CREATE INDEX IF NOT EXISTS reservation_lifecycle_current_state_lookup
    ON reservation_lifecycle.reservation_lifecycle_transitions (reservation_id, version DESC)
    INCLUDE (current_state, transition_checksum);
