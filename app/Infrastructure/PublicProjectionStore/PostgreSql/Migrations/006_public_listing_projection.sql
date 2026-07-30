CREATE SCHEMA IF NOT EXISTS public_projection;

CREATE TABLE IF NOT EXISTS public_projection.generations (
    generation_id uuid PRIMARY KEY,
    state varchar(16) NOT NULL CHECK (state IN ('active','candidate','retired')),
    created_at timestamptz(6) NOT NULL DEFAULT clock_timestamp()
);

CREATE UNIQUE INDEX IF NOT EXISTS public_projection_one_active_generation
    ON public_projection.generations ((state)) WHERE state = 'active';

CREATE TABLE IF NOT EXISTS public_projection.listing_projections (
    generation_id uuid NOT NULL REFERENCES public_projection.generations(generation_id) ON DELETE RESTRICT,
    canonical_path text NOT NULL,
    listing_id uuid NOT NULL,
    state varchar(16) NOT NULL CHECK (state IN ('current','historical','tombstone')),
    read_model bytea NULL,
    listing_version integer NOT NULL CHECK (listing_version >= 0),
    property_version integer NOT NULL CHECK (property_version >= 0),
    media_version integer NOT NULL CHECK (media_version >= 0),
    search_version integer NOT NULL CHECK (search_version > 0),
    content_seo_version integer NOT NULL CHECK (content_seo_version > 0),
    public_geography_version integer NULL CHECK (public_geography_version IS NULL OR public_geography_version > 0),
    public_media_version integer NULL CHECK (public_media_version IS NULL OR public_media_version > 0),
    payload_checksum char(64) NOT NULL,
    updated_at timestamptz(6) NOT NULL DEFAULT clock_timestamp(),
    PRIMARY KEY (generation_id, canonical_path),
    CHECK ((state = 'current') = (read_model IS NOT NULL))
);

CREATE UNIQUE INDEX IF NOT EXISTS public_projection_one_live_record_per_listing
    ON public_projection.listing_projections (generation_id, listing_id)
    WHERE state <> 'historical';

CREATE INDEX IF NOT EXISTS public_projection_public_lookup
    ON public_projection.listing_projections (canonical_path, generation_id)
    WHERE state = 'current';
