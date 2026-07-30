CREATE TABLE IF NOT EXISTS administration_audit.administrative_action_lifecycle_transitions (
    action_id uuid NOT NULL,
    version bigint NOT NULL CHECK (version >= 0),
    entry_kind text NOT NULL CHECK (entry_kind IN ('enrollment', 'transition')),
    previous_state text NULL,
    current_state text NOT NULL CHECK (current_state IN ('draft', 'pending_approval', 'recorded', 'approved', 'rejected')),
    action text NULL,
    entry_checksum char(64) NOT NULL CHECK (entry_checksum ~ '^[0-9a-f]{64}$'),
    source_checksum char(64) NULL CHECK (source_checksum ~ '^[0-9a-f]{64}$'),
    mirror_checksum char(64) NULL CHECK (mirror_checksum ~ '^[0-9a-f]{64}$'),
    PRIMARY KEY (action_id, version),
    CONSTRAINT administrative_action_lifecycle_entry_shape CHECK (
        (
            entry_kind = 'enrollment'
            AND previous_state IS NULL
            AND action IS NULL
            AND source_checksum IS NOT NULL
            AND mirror_checksum IS NULL
        )
        OR
        (
            entry_kind = 'transition'
            AND previous_state IS NOT NULL
            AND action IS NOT NULL
            AND source_checksum IS NULL
            AND mirror_checksum IS NOT NULL
        )
    ),
    CONSTRAINT administrative_action_lifecycle_allowed_transition CHECK (
        entry_kind = 'enrollment'
        OR (previous_state, action, current_state) IN (
            ('draft', 'record', 'recorded'),
            ('draft', 'record', 'pending_approval'),
            ('pending_approval', 'approve', 'approved'),
            ('pending_approval', 'reject', 'rejected')
        )
    )
);

CREATE INDEX IF NOT EXISTS administrative_action_lifecycle_current_lookup
    ON administration_audit.administrative_action_lifecycle_transitions (action_id, version DESC)
    INCLUDE (current_state, entry_checksum, mirror_checksum);
