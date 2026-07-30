CREATE SCHEMA IF NOT EXISTS identity_access;

CREATE TABLE IF NOT EXISTS identity_access.account_status_lifecycle_transitions (
    account_id uuid NOT NULL,
    version bigint NOT NULL CHECK (version >= 0),
    entry_kind text NOT NULL CHECK (entry_kind IN ('bootstrap','transition')),
    previous_state text NULL,
    current_state text NOT NULL CHECK (current_state IN ('active','suspended')),
    action text NULL,
    actor_id text NULL,
    occurred_at timestamptz NULL,
    intent_id text NULL,
    context_version smallint NULL,
    legacy_account_version bigint NULL CHECK (legacy_account_version >= 0),
    entry_checksum char(64) NOT NULL CHECK (entry_checksum ~ '^[0-9a-f]{64}$'),
    PRIMARY KEY (account_id, version),
    CONSTRAINT account_status_entry_shape CHECK (
        (
            entry_kind = 'bootstrap'
            AND version = 0
            AND previous_state IS NULL
            AND action IS NULL
            AND actor_id IS NULL
            AND occurred_at IS NULL
            AND intent_id IS NULL
            AND context_version IS NULL
            AND legacy_account_version IS NOT NULL
        )
        OR
        (
            entry_kind = 'transition'
            AND version > 0
            AND previous_state IS NOT NULL
            AND action IS NOT NULL
            AND actor_id IS NOT NULL
            AND occurred_at IS NOT NULL
            AND intent_id IS NOT NULL
            AND context_version = 1
            AND legacy_account_version IS NULL
        )
    ),
    CONSTRAINT account_status_allowed_transition CHECK (
        entry_kind = 'bootstrap'
        OR (previous_state, action, current_state) IN (
            ('active','suspend','suspended'),
            ('suspended','reactivate','active')
        )
    )
);

CREATE UNIQUE INDEX IF NOT EXISTS account_status_intent_uniqueness
    ON identity_access.account_status_lifecycle_transitions (account_id, intent_id)
    WHERE intent_id IS NOT NULL;

CREATE INDEX IF NOT EXISTS account_status_current_lookup
    ON identity_access.account_status_lifecycle_transitions (account_id, version DESC)
    INCLUDE (current_state, entry_checksum);
