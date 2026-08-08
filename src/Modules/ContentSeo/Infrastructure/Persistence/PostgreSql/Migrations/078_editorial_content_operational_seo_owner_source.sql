CREATE SCHEMA IF NOT EXISTS content_seo;

CREATE TABLE IF NOT EXISTS content_seo.editorial_seo_revision_journal (
    resource_key varchar(255) NOT NULL,
    stream_type varchar(24) NOT NULL CHECK (stream_type IN ('editorial', 'operational_seo')),
    revision bigint NOT NULL CHECK (revision > 0),
    decision varchar(24) NOT NULL CHECK (decision IN ('published', 'unpublished', 'indexable', 'no_index')),
    effective_at timestamptz(6) NOT NULL,
    recorded_at timestamptz(6) NOT NULL,
    revision_checksum char(64) NOT NULL CHECK (revision_checksum ~ '^[0-9a-f]{64}$'),
    PRIMARY KEY (resource_key, stream_type, revision),
    UNIQUE (resource_key, stream_type, effective_at),
    CHECK (recorded_at >= effective_at),
    CHECK ((stream_type = 'editorial' AND decision IN ('published', 'unpublished')) OR
           (stream_type = 'operational_seo' AND decision IN ('indexable', 'no_index')))
);

CREATE TABLE IF NOT EXISTS content_seo.editorial_seo_current_index (
    resource_key varchar(255) NOT NULL,
    stream_type varchar(24) NOT NULL CHECK (stream_type IN ('editorial', 'operational_seo')),
    revision bigint NOT NULL CHECK (revision > 0),
    decision varchar(24) NOT NULL CHECK (decision IN ('published', 'unpublished', 'indexable', 'no_index')),
    effective_at timestamptz(6) NOT NULL,
    recorded_at timestamptz(6) NOT NULL,
    revision_checksum char(64) NOT NULL CHECK (revision_checksum ~ '^[0-9a-f]{64}$'),
    PRIMARY KEY (resource_key, stream_type)
);

CREATE INDEX IF NOT EXISTS editorial_seo_temporal_read
    ON content_seo.editorial_seo_revision_journal
    (resource_key, stream_type, effective_at DESC, recorded_at DESC, revision DESC);
