CREATE TABLE IF NOT EXISTS moderation_reports.queue_claim_intents (
    queue_item_id uuid NOT NULL,
    intent_id uuid NOT NULL,
    intent_checksum char(64) NOT NULL CHECK (intent_checksum ~ '^[0-9a-f]{64}$'),
    recorded_at timestamptz(6) NOT NULL,
    PRIMARY KEY (queue_item_id, intent_id)
);

CREATE INDEX IF NOT EXISTS moderation_reports_queue_claim_intents_recorded_idx
    ON moderation_reports.queue_claim_intents (recorded_at, queue_item_id);
