CREATE SCHEMA IF NOT EXISTS contacts_leads;

CREATE TABLE IF NOT EXISTS contacts_leads.lead_lifecycle_transitions (
    lead_id uuid NOT NULL,
    version bigint NOT NULL CHECK (version > 0),
    previous_state text NULL,
    current_state text NOT NULL CHECK (current_state IN ('created','delivered','rejected','closed')),
    action text NULL,
    transition_checksum char(64) NOT NULL CHECK (transition_checksum ~ '^[0-9a-f]{64}$'),
    PRIMARY KEY (lead_id, version),
    CONSTRAINT lead_lifecycle_shape CHECK (
        (version = 1 AND previous_state IS NULL AND action IS NULL)
        OR
        (version > 1 AND previous_state IS NOT NULL AND action IS NOT NULL)
    ),
    CONSTRAINT lead_lifecycle_allowed_transition CHECK (
        version = 1
        OR (previous_state, action, current_state) IN (
            ('created','deliver','delivered'),
            ('created','reject','rejected'),
            ('delivered','close','closed'),
            ('rejected','close','closed')
        )
    )
);

CREATE INDEX IF NOT EXISTS lead_lifecycle_current_state_lookup
    ON contacts_leads.lead_lifecycle_transitions (lead_id, version DESC)
    INCLUDE (current_state, transition_checksum);
