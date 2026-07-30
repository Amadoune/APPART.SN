CREATE TABLE IF NOT EXISTS moderation_reports.listing_handoff_results (
    revision bigint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    message_id varchar(78) NOT NULL,
    case_id uuid NOT NULL,
    decision_id uuid NOT NULL,
    command_id uuid NULL,
    command_checksum char(64) NULL CHECK (
        command_checksum IS NULL OR command_checksum ~ '^[0-9a-f]{64}$'
    ),
    status varchar(32) NOT NULL CHECK (status IN (
        'requested',
        'applied',
        'already_applied',
        'rejected',
        'version_conflict',
        'authorization_denied',
        'target_ineligible',
        'dependency_unavailable',
        'quarantined'
    )),
    recorded_at timestamptz(6) NOT NULL,
    UNIQUE (message_id, status)
);

CREATE INDEX IF NOT EXISTS moderation_listing_handoff_result_latest_idx
    ON moderation_reports.listing_handoff_results(message_id, revision DESC);
