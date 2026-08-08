CREATE SCHEMA IF NOT EXISTS reservation_lifecycle;

CREATE TABLE IF NOT EXISTS reservation_lifecycle.availability_intent_revisions (
    availability_intent_id uuid NOT NULL,
    revision bigint NOT NULL CHECK (revision > 0),
    availability_subject_id uuid NOT NULL,
    window_start timestamptz(6) NOT NULL,
    window_end timestamptz(6) NOT NULL,
    decision text NOT NULL CHECK (decision IN ('proposed','held','committed','released','expired')),
    effective_at timestamptz(6) NOT NULL,
    recorded_at timestamptz(6) NOT NULL,
    revision_checksum char(64) NOT NULL CHECK (revision_checksum ~ '^[0-9a-f]{64}$'),
    PRIMARY KEY (availability_intent_id, revision),
    UNIQUE (availability_intent_id, effective_at),
    CHECK (window_start < window_end),
    CHECK (recorded_at >= effective_at)
);

CREATE INDEX IF NOT EXISTS reservation_availability_subject_temporal_read
    ON reservation_lifecycle.availability_intent_revisions
    (availability_subject_id, effective_at DESC, revision DESC)
    INCLUDE (availability_intent_id, window_start, window_end, decision, recorded_at, revision_checksum);

CREATE INDEX IF NOT EXISTS reservation_availability_overlap_lookup
    ON reservation_lifecycle.availability_intent_revisions
    (availability_subject_id, window_start, window_end);
