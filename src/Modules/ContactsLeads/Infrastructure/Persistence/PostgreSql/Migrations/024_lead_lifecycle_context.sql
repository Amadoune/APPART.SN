CREATE TABLE IF NOT EXISTS contacts_leads.lead_lifecycle_transition_contexts (
 lead_id uuid NOT NULL,
 version bigint NOT NULL CHECK (version > 1),
 actor_id uuid NOT NULL,
 occurred_at timestamptz NOT NULL,
 context_checksum char(64) NOT NULL CHECK (context_checksum ~ '^[0-9a-f]{64}$'),
 PRIMARY KEY (lead_id, version)
);
ALTER TABLE contacts_leads.lead_lifecycle_transition_contexts
    DROP CONSTRAINT IF EXISTS lead_lifecycle_transition_contexts_lead_id_version_fkey;
CREATE INDEX IF NOT EXISTS lead_lifecycle_context_actor_lookup ON contacts_leads.lead_lifecycle_transition_contexts(actor_id, occurred_at, lead_id, version);
