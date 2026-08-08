CREATE TABLE IF NOT EXISTS search_discovery.search_query_resolution_outbox (
    outbox_id char(64) PRIMARY KEY CHECK (outbox_id ~ '^[0-9a-f]{64}$'),
    status varchar(32) NOT NULL CHECK (status IN ('found', 'empty', 'corrupted', 'dependency_unavailable')),
    observed_at timestamptz(6) NOT NULL,
    payload jsonb NOT NULL CHECK (jsonb_typeof(payload) = 'object'),
    created_at timestamptz(6) NOT NULL DEFAULT clock_timestamp(),
    delivered_at timestamptz(6) NULL
);

CREATE INDEX IF NOT EXISTS search_query_resolution_outbox_pending_idx
    ON search_discovery.search_query_resolution_outbox (created_at, outbox_id)
    WHERE delivered_at IS NULL;
