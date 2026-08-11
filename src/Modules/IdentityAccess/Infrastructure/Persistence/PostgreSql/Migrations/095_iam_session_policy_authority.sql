ALTER TABLE identity_access_completion.sessions
    ADD COLUMN IF NOT EXISTS policy_version varchar(64),
    ADD COLUMN IF NOT EXISTS original_issued_at timestamptz,
    ADD COLUMN IF NOT EXISTS idle_expires_at timestamptz,
    ADD COLUMN IF NOT EXISTS absolute_expires_at timestamptz;

DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM pg_constraint WHERE conname = 'sessions_policy_version_ck'
    ) THEN
        ALTER TABLE identity_access_completion.sessions
            ADD CONSTRAINT sessions_policy_version_ck
            CHECK (policy_version IS NULL OR policy_version = 'session-policy-v1');
    END IF;
END
$$;

CREATE INDEX IF NOT EXISTS sessions_active_account_issued_idx
    ON identity_access_completion.sessions(account_id, original_issued_at, session_id)
    WHERE state = 'Active';
