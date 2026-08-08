CREATE TABLE IF NOT EXISTS content_seo.content_seo_outbox (
    message_id char(64) PRIMARY KEY CHECK (message_id ~ '^[0-9a-f]{64}$'),
    delivery_kind varchar(24) NOT NULL CHECK (delivery_kind IN ('editorial','operational_seo')),
    event_type varchar(64) NOT NULL CHECK (event_type IN ('content_seo.editorial_content.observed.v1','content_seo.operational_seo.observed.v1')),
    delivery_status varchar(32) NOT NULL CHECK (delivery_status IN ('published','unpublished','indexable','no_index','missing','corrupted','dependency_unavailable')),
    observed_at timestamptz(6) NOT NULL,
    payload jsonb NOT NULL CHECK (jsonb_typeof(payload) = 'object'),
    message_checksum char(64) NOT NULL CHECK (message_checksum ~ '^[0-9a-f]{64}$'),
    retry_count smallint NOT NULL DEFAULT 0 CHECK (retry_count >= 0 AND retry_count <= 10),
    created_at timestamptz(6) NOT NULL DEFAULT clock_timestamp(),
    delivered_at timestamptz(6) NULL,
    CHECK ((delivery_kind = 'editorial' AND event_type = 'content_seo.editorial_content.observed.v1' AND delivery_status IN ('published','unpublished','missing','corrupted','dependency_unavailable')) OR
           (delivery_kind = 'operational_seo' AND event_type = 'content_seo.operational_seo.observed.v1' AND delivery_status IN ('indexable','no_index','missing','corrupted','dependency_unavailable')))
);
CREATE INDEX IF NOT EXISTS content_seo_outbox_pending_idx ON content_seo.content_seo_outbox(created_at,message_id) WHERE delivered_at IS NULL AND retry_count < 10;
