CREATE TABLE IF NOT EXISTS administration_audit.administrative_action_lifecycle_event_inbox (
    inbox_id varchar(80) PRIMARY KEY,
    message_id varchar(128) NOT NULL UNIQUE,
    message_type varchar(64) NOT NULL CHECK (message_type IN (
        'administrative.action.lifecycle.recorded',
        'administrative.action.lifecycle.approval_requested',
        'administrative.action.lifecycle.approved',
        'administrative.action.lifecycle.rejected'
    )),
    transport_version smallint NOT NULL CHECK (transport_version = 1),
    canonical_event text NOT NULL,
    source varchar(32) NOT NULL CHECK (source = 'AdministrationAudit'),
    business_event_id char(64) NOT NULL CHECK (business_event_id ~ '^[0-9a-f]{64}$'),
    payload_checksum char(64) NOT NULL CHECK (payload_checksum ~ '^[0-9a-f]{64}$'),
    transport_envelope text NOT NULL,
    status varchar(16) NOT NULL DEFAULT 'pending' CHECK (status = 'pending'),
    delivery_attempts integer NOT NULL DEFAULT 0 CHECK (delivery_attempts = 0),
    CHECK (message_id ~ '^administrative-action-lifecycle-delivery-[0-9a-f]{64}$')
);

CREATE INDEX IF NOT EXISTS administrative_action_lifecycle_event_inbox_recovery
    ON administration_audit.administrative_action_lifecycle_event_inbox (status, message_id);
