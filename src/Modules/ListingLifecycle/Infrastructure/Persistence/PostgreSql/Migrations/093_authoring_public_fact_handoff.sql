CREATE TABLE IF NOT EXISTS listing_lifecycle.authoring_public_fact_handoffs (
    listing_id uuid PRIMARY KEY REFERENCES listing_lifecycle.listings(id) ON DELETE RESTRICT,
    authoring_version integer NOT NULL CHECK (authoring_version > 0),
    transaction_kind varchar(8) NOT NULL CHECK (transaction_kind IN ('sale','rent')),
    source_intent_id uuid NOT NULL,
    source_checksum char(64) NOT NULL,
    observed_at timestamptz(6) NOT NULL,
    candidate_checksum char(64) NOT NULL,
    status varchar(16) NOT NULL CHECK (status IN ('candidate','published')),
    published_revision_id uuid NULL,
    published_at timestamptz(6) NULL,
    CHECK ((status = 'published') = (published_revision_id IS NOT NULL AND published_at IS NOT NULL))
);

ALTER TABLE public_projection.listing_projections
    ADD COLUMN IF NOT EXISTS transaction_kind varchar(8) NULL CHECK (transaction_kind IS NULL OR transaction_kind IN ('sale','rent')),
    ADD COLUMN IF NOT EXISTS city text NULL,
    ADD COLUMN IF NOT EXISTS property_type text NULL;

CREATE INDEX IF NOT EXISTS public_projection_filterable_search
    ON public_projection.listing_projections (transaction_kind, city, property_type, canonical_path)
    WHERE state = 'current';
