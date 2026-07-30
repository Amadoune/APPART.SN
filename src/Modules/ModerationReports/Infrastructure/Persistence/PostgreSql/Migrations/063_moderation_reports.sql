CREATE SCHEMA IF NOT EXISTS moderation_reports;

CREATE TABLE IF NOT EXISTS moderation_reports.cases (
    case_id uuid PRIMARY KEY,
    target_type varchar(32) NOT NULL CHECK (target_type IN ('Listing','Media','Account','ProfessionalProfile')),
    target_id uuid NOT NULL,
    status varchar(24) NOT NULL CHECK (status IN ('Open','UnderReview','Decided','Closed')),
    current_decision_id uuid NULL,
    version bigint NOT NULL CHECK (version > 0),
    last_intent_id uuid NOT NULL,
    last_intent_checksum char(64) NOT NULL CHECK (last_intent_checksum ~ '^[0-9a-f]{64}$'),
    updated_at timestamptz(6) NOT NULL
);

CREATE TABLE IF NOT EXISTS moderation_reports.report_revisions (
    case_id uuid NOT NULL,
    report_id uuid NOT NULL,
    case_version bigint NOT NULL CHECK (case_version > 0),
    payload jsonb NOT NULL CHECK (jsonb_typeof(payload) = 'object'),
    payload_checksum char(64) NOT NULL CHECK (payload_checksum ~ '^[0-9a-f]{64}$'),
    recorded_at timestamptz(6) NOT NULL,
    PRIMARY KEY (case_id, report_id, case_version)
);

CREATE TABLE IF NOT EXISTS moderation_reports.finding_revisions (
    case_id uuid NOT NULL,
    finding_id uuid NOT NULL,
    case_version bigint NOT NULL CHECK (case_version > 0),
    payload jsonb NOT NULL CHECK (jsonb_typeof(payload) = 'object'),
    payload_checksum char(64) NOT NULL CHECK (payload_checksum ~ '^[0-9a-f]{64}$'),
    recorded_at timestamptz(6) NOT NULL,
    PRIMARY KEY (case_id, finding_id, case_version)
);

CREATE TABLE IF NOT EXISTS moderation_reports.decision_revisions (
    case_id uuid NOT NULL,
    decision_id uuid NOT NULL,
    case_version bigint NOT NULL CHECK (case_version > 0),
    payload jsonb NOT NULL CHECK (jsonb_typeof(payload) = 'object'),
    payload_checksum char(64) NOT NULL CHECK (payload_checksum ~ '^[0-9a-f]{64}$'),
    recorded_at timestamptz(6) NOT NULL,
    PRIMARY KEY (case_id, decision_id, case_version)
);

CREATE TABLE IF NOT EXISTS moderation_reports.decision_supersessions (
    case_id uuid NOT NULL,
    decision_id uuid NOT NULL,
    superseded_decision_id uuid NOT NULL,
    case_version bigint NOT NULL CHECK (case_version > 0),
    recorded_at timestamptz(6) NOT NULL,
    PRIMARY KEY (case_id, decision_id)
);

CREATE TABLE IF NOT EXISTS moderation_reports.case_intents (
    case_id uuid NOT NULL,
    intent_id uuid NOT NULL,
    intent_checksum char(64) NOT NULL CHECK (intent_checksum ~ '^[0-9a-f]{64}$'),
    result_version bigint NOT NULL CHECK (result_version > 0),
    recorded_at timestamptz(6) NOT NULL,
    PRIMARY KEY (case_id, intent_id)
);

CREATE TABLE IF NOT EXISTS moderation_reports.queue_items (
    queue_item_id uuid PRIMARY KEY,
    case_id uuid NOT NULL UNIQUE,
    priority smallint NOT NULL CHECK (priority BETWEEN 0 AND 100),
    category varchar(64) NOT NULL,
    state varchar(16) NOT NULL CHECK (state IN ('Available','Claimed','Completed')),
    lease_id uuid NULL,
    claim_owner_id uuid NULL,
    lease_expires_at timestamptz(6) NULL,
    source_version bigint NOT NULL CHECK (source_version > 0),
    updated_at timestamptz(6) NOT NULL,
    CHECK (
        (state = 'Claimed' AND lease_id IS NOT NULL AND claim_owner_id IS NOT NULL AND lease_expires_at IS NOT NULL)
        OR
        (state <> 'Claimed' AND lease_id IS NULL AND claim_owner_id IS NULL AND lease_expires_at IS NULL)
    )
);

CREATE TABLE IF NOT EXISTS moderation_reports.queue_checkpoints (
    projection_name varchar(64) PRIMARY KEY,
    checkpoint bigint NOT NULL CHECK (checkpoint >= 0),
    updated_at timestamptz(6) NOT NULL
);

CREATE INDEX IF NOT EXISTS moderation_reports_cases_target_idx
    ON moderation_reports.cases (target_type, target_id);

CREATE INDEX IF NOT EXISTS moderation_reports_reports_latest_idx
    ON moderation_reports.report_revisions (case_id, report_id, case_version DESC);

CREATE INDEX IF NOT EXISTS moderation_reports_findings_latest_idx
    ON moderation_reports.finding_revisions (case_id, finding_id, case_version DESC);

CREATE INDEX IF NOT EXISTS moderation_reports_decisions_latest_idx
    ON moderation_reports.decision_revisions (case_id, decision_id, case_version DESC);

CREATE INDEX IF NOT EXISTS moderation_reports_queue_claim_idx
    ON moderation_reports.queue_items (state, priority DESC, updated_at, queue_item_id);

CREATE INDEX IF NOT EXISTS moderation_reports_queue_lease_idx
    ON moderation_reports.queue_items (lease_expires_at)
    WHERE state = 'Claimed';
