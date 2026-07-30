CREATE SCHEMA IF NOT EXISTS listing_lifecycle;

CREATE TABLE IF NOT EXISTS listing_lifecycle.publication_workflow_transitions (
    listing_id uuid NOT NULL,
    version bigint NOT NULL CHECK (version > 0),
    previous_state text NULL,
    current_state text NOT NULL CHECK (current_state IN ('draft','submitted','under_review','changes_requested','published','suspended','expired','withdrawn','rejected','archived')),
    action text NULL,
    transition_checksum char(64) NOT NULL CHECK (transition_checksum ~ '^[0-9a-f]{64}$'),
    PRIMARY KEY (listing_id, version),
    CONSTRAINT publication_workflow_shape CHECK (
        (version = 1 AND previous_state IS NULL AND action IS NULL)
        OR
        (version > 1 AND previous_state IS NOT NULL AND action IS NOT NULL)
    ),
    CONSTRAINT publication_workflow_allowed_transition CHECK (
        version = 1
        OR (previous_state, action, current_state) IN (
            ('draft','submit','submitted'),
            ('draft','withdraw','withdrawn'),
            ('draft','archive','archived'),
            ('submitted','begin_review','under_review'),
            ('submitted','withdraw','withdrawn'),
            ('under_review','approve_and_publish','published'),
            ('under_review','request_changes','changes_requested'),
            ('under_review','reject','rejected'),
            ('under_review','withdraw','withdrawn'),
            ('changes_requested','submit','submitted'),
            ('changes_requested','withdraw','withdrawn'),
            ('changes_requested','archive','archived'),
            ('published','review_material_change','under_review'),
            ('published','suspend','suspended'),
            ('published','expire','expired'),
            ('published','withdraw','withdrawn'),
            ('suspended','reinstate','published'),
            ('suspended','request_changes','changes_requested'),
            ('suspended','reject','rejected'),
            ('suspended','archive','archived'),
            ('expired','review_renewal','under_review'),
            ('expired','renew_directly','published'),
            ('expired','withdraw','withdrawn'),
            ('expired','archive','archived'),
            ('withdrawn','approve_republication','under_review'),
            ('withdrawn','archive','archived'),
            ('rejected','archive','archived')
        )
    )
);

CREATE INDEX IF NOT EXISTS publication_workflow_current_state_lookup
    ON listing_lifecycle.publication_workflow_transitions (listing_id, version DESC)
    INCLUDE (current_state, transition_checksum);
