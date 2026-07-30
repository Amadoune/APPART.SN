CREATE TABLE IF NOT EXISTS geography.place_lifecycle_event_inbox (
    inbox_id varchar(80) PRIMARY KEY,
    message_id varchar(128) NOT NULL UNIQUE,
    message_type varchar(64) NOT NULL CHECK (message_type IN (
        'place.lifecycle.enabled',
        'place.lifecycle.disabled',
        'place.lifecycle.merged'
    )),
    transport_version smallint NOT NULL CHECK (transport_version = 1),
    canonical_event text NOT NULL,
    source varchar(32) NOT NULL CHECK (source = 'PlaceLifecycle'),
    event_id char(64) NOT NULL CHECK (event_id ~ '^[0-9a-f]{64}$'),
    payload_checksum char(64) NOT NULL CHECK (payload_checksum ~ '^[0-9a-f]{64}$'),
    transport_envelope text NOT NULL,
    status varchar(16) NOT NULL DEFAULT 'pending' CHECK (status = 'pending'),
    delivery_attempts integer NOT NULL DEFAULT 0 CHECK (delivery_attempts = 0),
    CHECK (message_id ~ '^place-lifecycle-delivery-[0-9a-f]{64}$')
);

CREATE INDEX IF NOT EXISTS place_lifecycle_event_inbox_recovery
    ON geography.place_lifecycle_event_inbox (status, message_id);
