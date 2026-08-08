CREATE SCHEMA IF NOT EXISTS administration_console;
CREATE TABLE IF NOT EXISTS administration_console.owner_revision_journal (
 subject_key varchar(255) NOT NULL, stream_type varchar(16) NOT NULL CHECK(stream_type IN ('operator','queue','audit')),
 revision bigint NOT NULL CHECK(revision>0), decision varchar(24) NOT NULL CHECK(decision IN ('available','unavailable','ready','empty')),
 effective_at timestamptz(6) NOT NULL, recorded_at timestamptz(6) NOT NULL, revision_checksum char(64) NOT NULL CHECK(revision_checksum ~ '^[0-9a-f]{64}$'),
 PRIMARY KEY(subject_key,stream_type,revision), UNIQUE(subject_key,stream_type,effective_at), CHECK(recorded_at>=effective_at),
 CHECK((stream_type='operator' AND decision IN ('available','unavailable')) OR (stream_type='queue' AND decision IN ('ready','empty')) OR (stream_type='audit' AND decision='available'))
);
CREATE TABLE IF NOT EXISTS administration_console.owner_current_index (
 subject_key varchar(255) NOT NULL, stream_type varchar(16) NOT NULL CHECK(stream_type IN ('operator','queue','audit')),
 revision bigint NOT NULL CHECK(revision>0), decision varchar(24) NOT NULL CHECK(decision IN ('available','unavailable','ready','empty')), effective_at timestamptz(6) NOT NULL, recorded_at timestamptz(6) NOT NULL,
 revision_checksum char(64) NOT NULL CHECK(revision_checksum ~ '^[0-9a-f]{64}$'), PRIMARY KEY(subject_key,stream_type),
 CHECK((stream_type='operator' AND decision IN ('available','unavailable')) OR (stream_type='queue' AND decision IN ('ready','empty')) OR (stream_type='audit' AND decision='available'))
);
CREATE INDEX IF NOT EXISTS administration_console_owner_temporal_read ON administration_console.owner_revision_journal(subject_key,stream_type,effective_at DESC,recorded_at DESC,revision DESC);
