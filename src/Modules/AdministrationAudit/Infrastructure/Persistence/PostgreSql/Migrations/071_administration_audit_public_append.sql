CREATE TABLE IF NOT EXISTS administration_audit.public_append_records (
    record_id uuid PRIMARY KEY,
    source_owner varchar(64) NOT NULL CHECK (source_owner IN ('ModerationReports')),
    operation varchar(48) NOT NULL CHECK (operation IN (
        'report_submitted',
        'report_validated',
        'finding_recorded',
        'decision_issued',
        'case_closed',
        'queue_item_claimed',
        'listing_handoff_completed'
    )),
    subject_id uuid NOT NULL,
    actor_id uuid NOT NULL,
    outcome varchar(32) NOT NULL CHECK (outcome IN (
        'applied',
        'already_applied',
        'rejected',
        'forbidden',
        'conflict',
        'dependency_unavailable'
    )),
    correlation_id uuid NOT NULL,
    causation_id uuid NULL,
    occurred_at timestamptz(6) NOT NULL,
    policy_version varchar(64) NOT NULL CHECK (policy_version ~ '^[A-Za-z0-9._-]{1,64}$'),
    contract_version smallint NOT NULL CHECK (contract_version = 1),
    record_checksum char(64) NOT NULL CHECK (record_checksum ~ '^[0-9a-f]{64}$'),
    recorded_at timestamptz(6) NOT NULL DEFAULT clock_timestamp()
);
