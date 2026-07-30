CREATE SCHEMA IF NOT EXISTS identity_access_completion;

CREATE TABLE IF NOT EXISTS identity_access_completion.atomic_operation_intents (
    intent_id uuid PRIMARY KEY,
    account_id uuid NOT NULL,
    operation varchar(32) NOT NULL CHECK (operation IN (
        'Authentication','Session','PasswordRecovery','ContactChange',
        'ClaimSwap','ProfileMutation','AccountClosure','Reopen'
    )),
    intent_checksum char(64) NOT NULL CHECK (intent_checksum ~ '^[0-9a-f]{64}$'),
    outcome varchar(16) NOT NULL CHECK (outcome IN ('Applied','Rejected')),
    completed_at timestamptz NOT NULL
);
CREATE INDEX IF NOT EXISTS atomic_operation_account_lookup
    ON identity_access_completion.atomic_operation_intents (account_id, completed_at DESC);
