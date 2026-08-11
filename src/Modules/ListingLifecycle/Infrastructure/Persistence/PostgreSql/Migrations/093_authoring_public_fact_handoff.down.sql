DROP INDEX IF EXISTS public_projection.public_projection_filterable_search;

ALTER TABLE public_projection.listing_projections
    DROP COLUMN IF EXISTS property_type,
    DROP COLUMN IF EXISTS city,
    DROP COLUMN IF EXISTS transaction_kind;

DROP TABLE IF EXISTS listing_lifecycle.authoring_public_fact_handoffs;
