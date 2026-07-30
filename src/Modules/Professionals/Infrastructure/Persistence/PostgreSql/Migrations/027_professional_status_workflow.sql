CREATE SCHEMA IF NOT EXISTS professionals;

CREATE TABLE IF NOT EXISTS professionals.professional_status_transitions (
    professional_id uuid NOT NULL,
    version bigint NOT NULL CHECK (version > 0),
    previous_state text NULL,
    current_state text NOT NULL CHECK (current_state IN ('active','suspended')),
    action text NULL,
    transition_checksum char(64) NOT NULL CHECK (transition_checksum ~ '^[0-9a-f]{64}$'),
    PRIMARY KEY (professional_id, version),
    CONSTRAINT professional_status_shape CHECK (
        (version = 1 AND previous_state IS NULL AND current_state = 'active' AND action IS NULL)
        OR
        (version > 1 AND previous_state IS NOT NULL AND action IS NOT NULL)
    ),
    CONSTRAINT professional_status_allowed_transition CHECK (
        version = 1
        OR (previous_state, action, current_state) IN (
            ('active','suspend','suspended'),
            ('suspended','reactivate','active')
        )
    )
);

CREATE INDEX IF NOT EXISTS professional_status_current_lookup
    ON professionals.professional_status_transitions (professional_id, version DESC)
    INCLUDE (current_state, transition_checksum);
