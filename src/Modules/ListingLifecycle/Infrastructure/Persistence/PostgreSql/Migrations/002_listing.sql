CREATE SCHEMA IF NOT EXISTS listing_lifecycle;

CREATE TABLE IF NOT EXISTS listing_lifecycle.listings (
    id uuid PRIMARY KEY,
    property_id uuid NOT NULL,
    status varchar(32) NOT NULL,
    last_changed_at timestamptz(6) NOT NULL,
    last_changed_at_offset smallint NOT NULL,
    expiration_date timestamptz(6) NULL,
    expiration_date_offset smallint NULL,
    version integer NOT NULL,
    CONSTRAINT listings_status_chk CHECK (status IN ('draft', 'submitted', 'under_review', 'changes_requested', 'published', 'suspended', 'expired', 'withdrawn', 'rejected', 'archived')),
    CONSTRAINT listings_version_chk CHECK (version >= 0)
);

CREATE TABLE IF NOT EXISTS listing_lifecycle.listing_revisions (
    listing_id uuid NOT NULL,
    sequence integer NOT NULL,
    revision_id uuid NOT NULL,
    previous_status varchar(32) NULL,
    status varchar(32) NOT NULL,
    actor_id varchar(100) NOT NULL,
    trigger varchar(64) NOT NULL,
    reason varchar(500) NOT NULL,
    origin varchar(32) NOT NULL,
    occurred_at timestamptz(6) NOT NULL,
    occurred_at_offset smallint NOT NULL,
    CONSTRAINT listing_revisions_pk PRIMARY KEY (listing_id, sequence),
    CONSTRAINT listing_revisions_revision_uq UNIQUE (listing_id, revision_id),
    CONSTRAINT listing_revisions_listing_fk FOREIGN KEY (listing_id) REFERENCES listing_lifecycle.listings (id) ON DELETE RESTRICT,
    CONSTRAINT listing_revisions_sequence_chk CHECK (sequence > 0),
    CONSTRAINT listing_revisions_previous_status_chk CHECK (previous_status IS NULL OR previous_status IN ('draft', 'submitted', 'under_review', 'changes_requested', 'published', 'suspended', 'expired', 'withdrawn', 'rejected', 'archived')),
    CONSTRAINT listing_revisions_status_chk CHECK (status IN ('draft', 'submitted', 'under_review', 'changes_requested', 'published', 'suspended', 'expired', 'withdrawn', 'rejected', 'archived')),
    CONSTRAINT listing_revisions_origin_chk CHECK (origin IN ('advertiser', 'moderation', 'system', 'administration'))
);

CREATE INDEX IF NOT EXISTS listing_revisions_listing_sequence_idx ON listing_lifecycle.listing_revisions (listing_id, sequence);

ALTER TABLE listing_lifecycle.listings ADD COLUMN IF NOT EXISTS last_changed_at_offset smallint NOT NULL DEFAULT 0;
ALTER TABLE listing_lifecycle.listings ADD COLUMN IF NOT EXISTS expiration_date_offset smallint NULL;
ALTER TABLE listing_lifecycle.listing_revisions ADD COLUMN IF NOT EXISTS occurred_at_offset smallint NOT NULL DEFAULT 0;
