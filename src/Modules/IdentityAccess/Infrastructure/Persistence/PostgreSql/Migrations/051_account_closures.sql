CREATE SCHEMA IF NOT EXISTS identity_access_completion;

CREATE TABLE IF NOT EXISTS identity_access_completion.account_closures (
    account_id uuid PRIMARY KEY,
    state varchar(24) NOT NULL CHECK (state IN ('Open','ClosureRequested','Closed','Reopened')),
    version bigint NOT NULL CHECK (version >= 1),
    requested_at timestamptz NULL,
    closed_at timestamptz NULL,
    reopened_at timestamptz NULL,
    actor_id uuid NOT NULL,
    reason_category varchar(64) NOT NULL,
    policy_version varchar(32) NOT NULL,
    cooling_off_until timestamptz NULL,
    retention_class varchar(32) NOT NULL,
    legal_hold boolean NOT NULL DEFAULT false,
    session_checkpoint bigint NOT NULL CHECK (session_checkpoint >= 0),
    last_intent_id uuid NOT NULL,
    last_intent_checksum char(64) NOT NULL CHECK (last_intent_checksum ~ '^[0-9a-f]{64}$'),
    updated_at timestamptz NOT NULL,
    CHECK (state <> 'ClosureRequested' OR requested_at IS NOT NULL),
    CHECK (state <> 'Closed' OR closed_at IS NOT NULL),
    CHECK (state <> 'Reopened' OR reopened_at IS NOT NULL)
);
