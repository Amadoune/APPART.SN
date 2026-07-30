CREATE TABLE IF NOT EXISTS listing_lifecycle.moderation_command_intents (
    command_id uuid PRIMARY KEY,
    checksum char(64) NOT NULL CHECK (checksum ~ '^[0-9a-f]{64}$'),
    recorded_at timestamptz(6) NOT NULL
);

CREATE TABLE IF NOT EXISTS listing_lifecycle.moderation_command_intent_results (
    command_id uuid PRIMARY KEY,
    result varchar(32) NOT NULL CHECK (result IN (
        'applied',
        'already_applied',
        'rejected',
        'divergent_intent',
        'version_conflict',
        'dependency_unavailable'
    )),
    recorded_at timestamptz(6) NOT NULL
);
