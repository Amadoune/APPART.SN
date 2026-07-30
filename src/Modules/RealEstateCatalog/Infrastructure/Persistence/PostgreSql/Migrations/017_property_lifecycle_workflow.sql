CREATE SCHEMA IF NOT EXISTS real_estate_catalog;

CREATE TABLE IF NOT EXISTS real_estate_catalog.property_lifecycle_transitions (
    property_id uuid NOT NULL,
    version bigint NOT NULL CHECK (version > 0),
    previous_state text NULL,
    current_state text NOT NULL CHECK (current_state IN ('draft','active','under_maintenance','unavailable','decommissioned','archived')),
    action text NULL,
    transition_checksum char(64) NOT NULL CHECK (transition_checksum ~ '^[0-9a-f]{64}$'),
    PRIMARY KEY (property_id, version),
    CONSTRAINT property_lifecycle_shape CHECK (
        (version = 1 AND previous_state IS NULL AND action IS NULL)
        OR
        (version > 1 AND previous_state IS NOT NULL AND action IS NOT NULL)
    ),
    CONSTRAINT property_lifecycle_allowed_transition CHECK (
        version = 1
        OR (previous_state, action, current_state) IN (
            ('draft','activate','active'),
            ('draft','archive','archived'),
            ('active','begin_maintenance','under_maintenance'),
            ('active','mark_unavailable','unavailable'),
            ('active','decommission','decommissioned'),
            ('under_maintenance','complete_maintenance','active'),
            ('under_maintenance','mark_unavailable','unavailable'),
            ('under_maintenance','decommission','decommissioned'),
            ('unavailable','restore_availability','active'),
            ('unavailable','begin_maintenance','under_maintenance'),
            ('unavailable','decommission','decommissioned'),
            ('decommissioned','archive','archived')
        )
    )
);

CREATE INDEX IF NOT EXISTS property_lifecycle_current_state_lookup
    ON real_estate_catalog.property_lifecycle_transitions (property_id, version DESC)
    INCLUDE (current_state, transition_checksum);
