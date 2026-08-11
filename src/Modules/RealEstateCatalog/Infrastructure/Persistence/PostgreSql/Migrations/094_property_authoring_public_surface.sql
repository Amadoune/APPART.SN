ALTER TABLE real_estate_catalog_authoring.property_authoring
    ADD COLUMN IF NOT EXISTS property_type varchar(32),
    ADD COLUMN IF NOT EXISTS city varchar(120),
    ADD COLUMN IF NOT EXISTS neighborhood varchar(120);

ALTER TABLE real_estate_catalog_authoring.property_authoring
    DROP CONSTRAINT IF EXISTS property_authoring_property_type_chk,
    DROP CONSTRAINT IF EXISTS property_authoring_city_chk,
    DROP CONSTRAINT IF EXISTS property_authoring_neighborhood_chk;

ALTER TABLE real_estate_catalog_authoring.property_authoring
    ADD CONSTRAINT property_authoring_property_type_chk CHECK (property_type IS NULL OR property_type IN ('apartment', 'house', 'villa', 'land', 'office', 'commercial')),
    ADD CONSTRAINT property_authoring_city_chk CHECK (city IS NULL OR length(btrim(city)) BETWEEN 2 AND 120),
    ADD CONSTRAINT property_authoring_neighborhood_chk CHECK (neighborhood IS NULL OR length(btrim(neighborhood)) BETWEEN 2 AND 120);
