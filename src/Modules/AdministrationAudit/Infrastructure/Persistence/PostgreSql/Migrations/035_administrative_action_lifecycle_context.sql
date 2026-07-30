CREATE TABLE IF NOT EXISTS administration_audit.administrative_action_lifecycle_transition_contexts (
    action_id uuid NOT NULL,
    version bigint NOT NULL CHECK (version > 0),
    contract_version smallint NOT NULL CHECK (contract_version = 1),
    expected_version bigint NOT NULL CHECK (expected_version >= 0 AND version = expected_version + 1),
    actor_id text NOT NULL CHECK (actor_id ~ '^[A-Za-z0-9][A-Za-z0-9._:-]{2,127}$'),
    occurred_at timestamptz NOT NULL,
    transition_action text NOT NULL CHECK (transition_action IN ('record','approve','reject')),
    approval_id uuid NULL,
    decision_id uuid NULL,
    historical_reason text NOT NULL CHECK (char_length(historical_reason) BETWEEN 10 AND 1000),
    decision_context_version smallint NOT NULL CHECK (decision_context_version = 1),
    reason_evidence text NOT NULL CHECK (reason_evidence IN ('present','missing')),
    recording_disposition text NOT NULL CHECK (recording_disposition IN ('direct_recording','independent_approval_required')),
    author_id text NOT NULL CHECK (author_id ~ '^[A-Za-z0-9][A-Za-z0-9._:-]{2,127}$'),
    decision_actor_id text NOT NULL CHECK (decision_actor_id ~ '^[A-Za-z0-9][A-Za-z0-9._:-]{2,127}$'),
    decision_context_checksum char(64) NOT NULL CHECK (decision_context_checksum ~ '^[0-9a-f]{64}$'),
    context_checksum char(64) NOT NULL CHECK (context_checksum ~ '^[0-9a-f]{64}$'),
    PRIMARY KEY (action_id, version),
    CONSTRAINT administrative_action_context_identity_shape CHECK (
        (transition_action = 'record' AND approval_id IS NULL AND decision_id IS NULL)
        OR (transition_action = 'approve' AND approval_id IS NOT NULL AND decision_id IS NOT NULL)
        OR (transition_action = 'reject' AND approval_id IS NULL AND decision_id IS NOT NULL)
    ),
    CONSTRAINT administrative_action_context_actor_shape CHECK (
        (transition_action = 'record' AND actor_id = author_id)
        OR (transition_action IN ('approve','reject') AND actor_id = decision_actor_id)
    )
);

CREATE INDEX IF NOT EXISTS administrative_action_lifecycle_context_latest
    ON administration_audit.administrative_action_lifecycle_transition_contexts (action_id, version DESC);
