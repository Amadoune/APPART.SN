CREATE SCHEMA IF NOT EXISTS contacts_leads;

CREATE TABLE IF NOT EXISTS contacts_leads.anti_abuse_decision_revisions (
    lead_ingress_intent_id uuid NOT NULL,
    revision bigint NOT NULL CHECK (revision > 0),
    decision text NOT NULL CHECK (decision IN ('allowed','blocked')),
    effective_at timestamptz(6) NOT NULL,
    recorded_at timestamptz(6) NOT NULL,
    policy_reference varchar(64) NULL CHECK (
        policy_reference IS NULL
        OR policy_reference ~ '^[a-z0-9][a-z0-9._:-]{0,63}$'
    ),
    revision_checksum char(64) NOT NULL CHECK (revision_checksum ~ '^[0-9a-f]{64}$'),
    PRIMARY KEY (lead_ingress_intent_id, revision),
    UNIQUE (lead_ingress_intent_id, effective_at),
    CHECK (recorded_at >= effective_at)
);

CREATE INDEX IF NOT EXISTS anti_abuse_decision_temporal_read
    ON contacts_leads.anti_abuse_decision_revisions
    (lead_ingress_intent_id, effective_at DESC, revision DESC)
    INCLUDE (decision, recorded_at, policy_reference, revision_checksum);
