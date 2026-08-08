CREATE SCHEMA IF NOT EXISTS legacy_migration;
CREATE TABLE IF NOT EXISTS legacy_migration.owner_revision_journal (
 subject_key varchar(255) NOT NULL, stream_type varchar(16) NOT NULL CHECK(stream_type IN ('inventory','wave','reconciliation','quarantine','cutover')),
 revision bigint NOT NULL CHECK(revision>0), decision varchar(24) NOT NULL CHECK(decision IN ('available','ready','blocked','completed','matched','divergent','pending','empty','contains_items')),
 effective_at timestamptz(6) NOT NULL, recorded_at timestamptz(6) NOT NULL, revision_checksum char(64) NOT NULL CHECK(revision_checksum ~ '^[0-9a-f]{64}$'),
 PRIMARY KEY(subject_key,stream_type,revision), UNIQUE(subject_key,stream_type,effective_at), CHECK(recorded_at>=effective_at),
 CHECK((stream_type='inventory' AND decision='available') OR (stream_type IN ('wave','cutover') AND decision IN ('ready','blocked','completed')) OR (stream_type='reconciliation' AND decision IN ('matched','divergent','pending')) OR (stream_type='quarantine' AND decision IN ('empty','contains_items')))
);
CREATE TABLE IF NOT EXISTS legacy_migration.owner_current_index (
 subject_key varchar(255) NOT NULL, stream_type varchar(16) NOT NULL CHECK(stream_type IN ('inventory','wave','reconciliation','quarantine','cutover')),
 revision bigint NOT NULL CHECK(revision>0), decision varchar(24) NOT NULL CHECK(decision IN ('available','ready','blocked','completed','matched','divergent','pending','empty','contains_items')),
 effective_at timestamptz(6) NOT NULL, recorded_at timestamptz(6) NOT NULL, revision_checksum char(64) NOT NULL CHECK(revision_checksum ~ '^[0-9a-f]{64}$'), PRIMARY KEY(subject_key,stream_type),
 CHECK((stream_type='inventory' AND decision='available') OR (stream_type IN ('wave','cutover') AND decision IN ('ready','blocked','completed')) OR (stream_type='reconciliation' AND decision IN ('matched','divergent','pending')) OR (stream_type='quarantine' AND decision IN ('empty','contains_items')))
);
CREATE INDEX IF NOT EXISTS legacy_migration_owner_temporal_read ON legacy_migration.owner_revision_journal(subject_key,stream_type,effective_at DESC,recorded_at DESC,revision DESC);
