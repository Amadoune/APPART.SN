CREATE TABLE IF NOT EXISTS moderation_reports.event_deliveries (
    event_id uuid NOT NULL,
    destination varchar(64) NOT NULL,
    event_checksum char(64) NOT NULL CHECK (event_checksum ~ '^[0-9a-f]{64}$'),
    message_id varchar(96) NOT NULL,
    status varchar(16) NOT NULL CHECK (status IN ('Delivered','Retry','Quarantined')),
    attempts smallint NOT NULL CHECK (attempts BETWEEN 0 AND 5),
    updated_at timestamptz(6) NOT NULL,
    PRIMARY KEY (event_id, destination)
);
