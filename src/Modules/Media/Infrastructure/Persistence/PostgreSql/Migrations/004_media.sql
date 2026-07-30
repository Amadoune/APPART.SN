CREATE SCHEMA IF NOT EXISTS media;
CREATE TABLE IF NOT EXISTS media.media_collections (id uuid PRIMARY KEY, property_id uuid NOT NULL, last_changed_at timestamptz(6) NOT NULL, last_changed_at_offset smallint NOT NULL, version integer NOT NULL CHECK (version >= 0));
CREATE TABLE IF NOT EXISTS media.media_id_reservations (media_id uuid PRIMARY KEY, collection_id uuid NOT NULL REFERENCES media.media_collections(id) ON DELETE RESTRICT);
CREATE TABLE IF NOT EXISTS media.media_items (
 media_id uuid PRIMARY KEY REFERENCES media.media_id_reservations(media_id) ON DELETE RESTRICT,
 collection_id uuid NOT NULL REFERENCES media.media_collections(id) ON DELETE RESTRICT,
 type varchar(16) NOT NULL CHECK (type IN ('image')), checksum char(64) NOT NULL CHECK (checksum ~ '^[0-9a-f]{64}$'),
 media_order integer NOT NULL CHECK (media_order > 0), caption varchar(500), source varchar(32) NOT NULL CHECK (source IN ('owner','professional','editorial','legacy_migration')),
 status varchar(16) NOT NULL CHECK (status IN ('active','removed','archived')), is_primary boolean NOT NULL,
 added_at timestamptz(6) NOT NULL, added_at_offset smallint NOT NULL, removed_at timestamptz(6), removed_at_offset smallint, archived_at timestamptz(6), archived_at_offset smallint,
 CONSTRAINT media_items_state_chk CHECK ((status='active' AND removed_at IS NULL AND archived_at IS NULL) OR (status='removed' AND removed_at IS NOT NULL AND archived_at IS NULL AND NOT is_primary) OR (status='archived' AND removed_at IS NULL AND archived_at IS NOT NULL AND NOT is_primary)),
 UNIQUE(collection_id, checksum)
);
CREATE UNIQUE INDEX IF NOT EXISTS media_items_active_order_uq ON media.media_items(collection_id, media_order) WHERE status='active';
CREATE UNIQUE INDEX IF NOT EXISTS media_items_active_primary_uq ON media.media_items(collection_id) WHERE status='active' AND is_primary;
CREATE INDEX IF NOT EXISTS media_items_collection_idx ON media.media_items(collection_id);
