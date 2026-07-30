CREATE SCHEMA IF NOT EXISTS professional_core;

CREATE TABLE IF NOT EXISTS professional_core.mandate_owner_sources (
    account_id uuid PRIMARY KEY,
    professional_ids jsonb NOT NULL,
    version bigint NOT NULL CHECK (version > 0),
    last_intent_id uuid NOT NULL,
    last_intent_checksum varchar(64) NOT NULL CHECK (length(last_intent_checksum) = 64),
    recorded_at timestamptz NOT NULL,
    CHECK (jsonb_typeof(professional_ids) = 'array')
);

CREATE TABLE IF NOT EXISTS professional_core.mandate_owner_source_intents (
    account_id uuid NOT NULL,
    intent_id uuid NOT NULL,
    intent_checksum varchar(64) NOT NULL CHECK (length(intent_checksum) = 64),
    recorded_at timestamptz NOT NULL,
    PRIMARY KEY (account_id, intent_id)
);

CREATE INDEX IF NOT EXISTS mandate_owner_source_intents_account_idx
    ON professional_core.mandate_owner_source_intents (account_id, recorded_at);
