CREATE SCHEMA IF NOT EXISTS notifications;
CREATE TABLE IF NOT EXISTS notifications.owner_revision_journal (
 subject_key varchar(255) NOT NULL, stream_type varchar(16) NOT NULL CHECK(stream_type IN ('preference','template','channel')),
 revision bigint NOT NULL CHECK(revision>0), decision varchar(24) NOT NULL CHECK(decision IN ('enabled','disabled','available','allowed','blocked')),
 effective_at timestamptz(6) NOT NULL, recorded_at timestamptz(6) NOT NULL, revision_checksum char(64) NOT NULL CHECK(revision_checksum ~ '^[0-9a-f]{64}$'),
 PRIMARY KEY(subject_key,stream_type,revision), UNIQUE(subject_key,stream_type,effective_at), CHECK(recorded_at>=effective_at),
 CHECK((stream_type='preference' AND decision IN ('enabled','disabled')) OR (stream_type='template' AND decision='available') OR (stream_type='channel' AND decision IN ('allowed','blocked')))
);
CREATE TABLE IF NOT EXISTS notifications.owner_current_index (
 subject_key varchar(255) NOT NULL, stream_type varchar(16) NOT NULL CHECK(stream_type IN ('preference','template','channel')),
 revision bigint NOT NULL CHECK(revision>0), decision varchar(24) NOT NULL CHECK(decision IN ('enabled','disabled','available','allowed','blocked')), effective_at timestamptz(6) NOT NULL, recorded_at timestamptz(6) NOT NULL,
 revision_checksum char(64) NOT NULL CHECK(revision_checksum ~ '^[0-9a-f]{64}$'), PRIMARY KEY(subject_key,stream_type)
 ,CHECK((stream_type='preference' AND decision IN ('enabled','disabled')) OR (stream_type='template' AND decision='available') OR (stream_type='channel' AND decision IN ('allowed','blocked')))
);
CREATE INDEX IF NOT EXISTS notifications_owner_temporal_read ON notifications.owner_revision_journal(subject_key,stream_type,effective_at DESC,recorded_at DESC,revision DESC);
