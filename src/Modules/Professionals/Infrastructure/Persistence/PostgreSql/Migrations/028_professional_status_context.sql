CREATE TABLE IF NOT EXISTS professionals.professional_status_transition_contexts (
    professional_id uuid NOT NULL,
    version bigint NOT NULL CHECK (version > 1),
    actor_id uuid NOT NULL,
    occurred_at timestamptz NOT NULL,
    context_checksum char(64) NOT NULL CHECK (context_checksum ~ '^[0-9a-f]{64}$'),
    PRIMARY KEY (professional_id, version)
);

CREATE INDEX IF NOT EXISTS professional_status_context_actor_lookup
    ON professionals.professional_status_transition_contexts (actor_id, occurred_at, professional_id, version);
