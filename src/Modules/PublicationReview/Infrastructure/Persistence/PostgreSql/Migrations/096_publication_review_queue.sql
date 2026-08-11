CREATE SCHEMA IF NOT EXISTS publication_review;

CREATE TABLE IF NOT EXISTS publication_review.queue_items (
    queue_item_id varchar(96) PRIMARY KEY,
    source_event_id varchar(96) NOT NULL UNIQUE,
    source_checksum char(64) NOT NULL CHECK (source_checksum ~ '^[0-9a-f]{64}$'),
    listing_id uuid NOT NULL,
    submission_version bigint NOT NULL CHECK (submission_version > 0),
    submitted_at timestamptz(6) NOT NULL,
    state varchar(16) NOT NULL CHECK (state IN ('pending','claimed','completed')),
    version bigint NOT NULL CHECK (version > 0),
    claimed_by varchar(255),
    claimed_at timestamptz(6),
    CHECK ((state = 'pending' AND claimed_by IS NULL AND claimed_at IS NULL) OR state <> 'pending'),
    UNIQUE (listing_id, submission_version)
);

CREATE INDEX IF NOT EXISTS publication_review_queue_order_idx
    ON publication_review.queue_items (submitted_at, listing_id, submission_version, queue_item_id);

CREATE TABLE IF NOT EXISTS publication_review.command_ledger (
    command_id uuid PRIMARY KEY,
    command_checksum char(64) NOT NULL CHECK (command_checksum ~ '^[0-9a-f]{64}$'),
    queue_item_id varchar(96) NOT NULL REFERENCES publication_review.queue_items(queue_item_id),
    result_status varchar(32) NOT NULL,
    result_version bigint,
    recorded_at timestamptz(6) NOT NULL
);

CREATE INDEX IF NOT EXISTS publication_review_command_item_idx
    ON publication_review.command_ledger (queue_item_id, recorded_at, command_id);
