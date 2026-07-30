CREATE SCHEMA IF NOT EXISTS media_ingestion;

CREATE TABLE IF NOT EXISTS media_ingestion.uploads (
    aggregate_id uuid PRIMARY KEY,
    state varchar(32) NOT NULL CHECK (state IN ('reserved','receiving','validating','completed','rejected','expired','abandoned')),
    version integer NOT NULL CHECK (version >= 1),
    last_intent_id uuid NOT NULL,
    last_intent_checksum char(64) NOT NULL CHECK (last_intent_checksum ~ '^[0-9a-f]{64}$'),
    payload jsonb NOT NULL CHECK (jsonb_typeof(payload) = 'object'),
    updated_at timestamptz(6) NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS media_ingestion.assets (
    aggregate_id uuid PRIMARY KEY,
    state varchar(32) NOT NULL CHECK (state IN ('quarantined','inspecting','ready','rejected','purge_pending','purged')),
    version integer NOT NULL CHECK (version >= 1),
    last_intent_id uuid NOT NULL,
    last_intent_checksum char(64) NOT NULL CHECK (last_intent_checksum ~ '^[0-9a-f]{64}$'),
    payload jsonb NOT NULL CHECK (jsonb_typeof(payload) = 'object'),
    updated_at timestamptz(6) NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS media_ingestion.processing (
    aggregate_id uuid PRIMARY KEY,
    state varchar(32) NOT NULL CHECK (state IN ('pending','leased','retry_scheduled','completed','rejected')),
    version integer NOT NULL CHECK (version >= 1),
    last_intent_id uuid NOT NULL,
    last_intent_checksum char(64) NOT NULL CHECK (last_intent_checksum ~ '^[0-9a-f]{64}$'),
    payload jsonb NOT NULL CHECK (jsonb_typeof(payload) = 'object'),
    updated_at timestamptz(6) NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS media_ingestion.quotas (
    aggregate_id uuid PRIMARY KEY,
    state varchar(32) NOT NULL CHECK (state IN ('available','exhausted','unavailable')),
    version integer NOT NULL CHECK (version >= 1),
    last_intent_id uuid NOT NULL,
    last_intent_checksum char(64) NOT NULL CHECK (last_intent_checksum ~ '^[0-9a-f]{64}$'),
    payload jsonb NOT NULL CHECK (jsonb_typeof(payload) = 'object'),
    updated_at timestamptz(6) NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS media_ingestion.upload_intents (
    intent_id uuid PRIMARY KEY, aggregate_id uuid NOT NULL, checksum char(64) NOT NULL CHECK (checksum ~ '^[0-9a-f]{64}$'),
    result_version integer NOT NULL CHECK (result_version >= 1), created_at timestamptz(6) NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS media_ingestion.asset_intents (
    intent_id uuid PRIMARY KEY, aggregate_id uuid NOT NULL, checksum char(64) NOT NULL CHECK (checksum ~ '^[0-9a-f]{64}$'),
    result_version integer NOT NULL CHECK (result_version >= 1), created_at timestamptz(6) NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS media_ingestion.processing_intents (
    intent_id uuid PRIMARY KEY, aggregate_id uuid NOT NULL, checksum char(64) NOT NULL CHECK (checksum ~ '^[0-9a-f]{64}$'),
    result_version integer NOT NULL CHECK (result_version >= 1), created_at timestamptz(6) NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS media_ingestion.quota_intents (
    intent_id uuid PRIMARY KEY, aggregate_id uuid NOT NULL, checksum char(64) NOT NULL CHECK (checksum ~ '^[0-9a-f]{64}$'),
    result_version integer NOT NULL CHECK (result_version >= 1), created_at timestamptz(6) NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS media_ingestion_upload_state_idx ON media_ingestion.uploads(state, updated_at);
CREATE INDEX IF NOT EXISTS media_ingestion_asset_state_idx ON media_ingestion.assets(state, updated_at);
CREATE INDEX IF NOT EXISTS media_ingestion_processing_state_idx ON media_ingestion.processing(state, updated_at);
