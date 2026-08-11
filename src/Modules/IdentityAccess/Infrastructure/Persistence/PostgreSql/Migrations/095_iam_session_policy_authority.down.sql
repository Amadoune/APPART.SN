DROP INDEX IF EXISTS identity_access_completion.sessions_active_account_issued_idx;

ALTER TABLE identity_access_completion.sessions
    DROP CONSTRAINT IF EXISTS sessions_policy_version_ck,
    DROP COLUMN IF EXISTS absolute_expires_at,
    DROP COLUMN IF EXISTS idle_expires_at,
    DROP COLUMN IF EXISTS original_issued_at,
    DROP COLUMN IF EXISTS policy_version;
