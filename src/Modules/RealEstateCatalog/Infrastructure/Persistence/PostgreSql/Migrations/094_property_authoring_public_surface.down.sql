ALTER TABLE real_estate_catalog_authoring.property_authoring
    DROP CONSTRAINT IF EXISTS property_authoring_property_type_chk,
    DROP CONSTRAINT IF EXISTS property_authoring_city_chk,
    DROP CONSTRAINT IF EXISTS property_authoring_neighborhood_chk,
    DROP COLUMN IF EXISTS property_type,
    DROP COLUMN IF EXISTS city,
    DROP COLUMN IF EXISTS neighborhood;
