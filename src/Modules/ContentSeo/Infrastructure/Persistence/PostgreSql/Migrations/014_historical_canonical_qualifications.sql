CREATE SCHEMA IF NOT EXISTS content_seo;

CREATE TABLE IF NOT EXISTS content_seo.historical_canonical_qualifications (
    decision_id uuid PRIMARY KEY,
    canonical text NOT NULL,
    qualification text NOT NULL,
    revision bigint NOT NULL,
    decision_checksum char(64) NOT NULL CHECK (decision_checksum ~ '^[0-9a-f]{64}$'),
    integrity_status text NOT NULL CHECK (integrity_status IN ('intact', 'corrupted')),
    CONSTRAINT historical_canonical_qualification_intact_shape CHECK (
        integrity_status = 'corrupted'
        OR (
            revision > 0
            AND canonical ~ '^https://appart[.]sn/[a-z0-9%/_-]+$'
            AND canonical = lower(canonical)
            AND qualification IN ('current', 'historical')
        )
    )
);

CREATE INDEX IF NOT EXISTS historical_canonical_qualifications_lookup
    ON content_seo.historical_canonical_qualifications (canonical, decision_id);
