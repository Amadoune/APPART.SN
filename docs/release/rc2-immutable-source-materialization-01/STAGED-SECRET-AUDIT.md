# Staged Secret Audit

The staged snapshot must be rescanned after index construction using the same closed patterns as the pre-staging audit. No credential, password, secret, token, cookie, private key, private RC2 storage key or sensitive connection string may be present.

The materialization must stop before commit if any match is unresolved. The final result is recorded externally after staging.
