CREATE SCHEMA IF NOT EXISTS administration_audit;

CREATE TABLE IF NOT EXISTS administration_audit.administrative_actions (
    id uuid PRIMARY KEY,
    author_id varchar(128) NOT NULL,
    target_id varchar(160) NOT NULL,
    action_type varchar(64) NOT NULL CHECK (action_type ~ '^[a-z][a-z0-9_.-]{2,63}$'),
    requires_four_eyes boolean NOT NULL,
    last_changed_at timestamptz NOT NULL,
    status varchar(32) NOT NULL CHECK (status IN ('draft', 'recorded', 'pending_approval', 'approved', 'rejected')),
    reason varchar(1000),
    version integer NOT NULL CHECK (version >= 0)
);

CREATE TABLE IF NOT EXISTS administration_audit.administrative_action_approvals (
    action_id uuid PRIMARY KEY REFERENCES administration_audit.administrative_actions(id) ON DELETE RESTRICT,
    approval_id uuid NOT NULL,
    approver_id varchar(128) NOT NULL,
    approved_at timestamptz NOT NULL
);

CREATE TABLE IF NOT EXISTS administration_audit.administrative_action_decisions (
    action_id uuid PRIMARY KEY REFERENCES administration_audit.administrative_actions(id) ON DELETE RESTRICT,
    decision_id uuid NOT NULL,
    outcome varchar(16) NOT NULL CHECK (outcome IN ('approved', 'rejected')),
    decided_by varchar(128) NOT NULL,
    reason varchar(1000) NOT NULL,
    decided_at timestamptz NOT NULL
);

CREATE TABLE IF NOT EXISTS administration_audit.administrative_action_audit_entries (
    action_id uuid NOT NULL REFERENCES administration_audit.administrative_actions(id) ON DELETE RESTRICT,
    sequence integer NOT NULL CHECK (sequence > 0),
    fact varchar(64) NOT NULL CHECK (fact ~ '^[a-z][a-z0-9_]{2,63}$'),
    actor_id varchar(128) NOT NULL,
    reason varchar(1000) NOT NULL,
    recorded_at timestamptz NOT NULL,
    PRIMARY KEY (action_id, sequence)
);
