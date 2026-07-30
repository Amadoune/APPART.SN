CREATE SCHEMA IF NOT EXISTS content_seo;

CREATE TABLE IF NOT EXISTS content_seo.historical_redirect_decisions (
    decision_id uuid PRIMARY KEY,
    historical_canonical text NOT NULL,
    destination_canonical text NULL,
    destination_qualification text NULL,
    revision bigint NOT NULL,
    decision_checksum char(64) NOT NULL CHECK (decision_checksum ~ '^[0-9a-f]{64}$'),
    integrity_status text NOT NULL CHECK (integrity_status IN ('intact', 'corrupted')),
    CONSTRAINT historical_redirect_intact_shape CHECK (
        integrity_status = 'corrupted'
        OR (
            revision > 0
            AND historical_canonical ~ '^https://appart[.]sn/[a-z0-9%/_-]+$'
            AND historical_canonical = lower(historical_canonical)
            AND (
                (destination_canonical IS NULL AND destination_qualification IS NULL)
                OR (
                    destination_canonical ~ '^https://appart[.]sn/[a-z0-9%/_-]+$'
                    AND destination_canonical = lower(destination_canonical)
                    AND destination_qualification IN ('current', 'historical')
                )
            )
        )
    )
);

CREATE INDEX IF NOT EXISTS historical_redirect_decisions_source_lookup
    ON content_seo.historical_redirect_decisions (historical_canonical, decision_id);
