ALTER TABLE real_estate_catalog_authoring.property_authoring
    ADD COLUMN IF NOT EXISTS property_reference varchar(64),
    ADD COLUMN IF NOT EXISTS surface_square_meters integer,
    ADD COLUMN IF NOT EXISTS rooms integer,
    ADD COLUMN IF NOT EXISTS bathrooms integer,
    ADD COLUMN IF NOT EXISTS construction_year integer,
    ADD COLUMN IF NOT EXISTS geographic_place_id uuid,
    ADD COLUMN IF NOT EXISTS address_line varchar(255),
    ADD COLUMN IF NOT EXISTS address_intent_id uuid;

ALTER TABLE real_estate_catalog_authoring.property_authoring
    DROP CONSTRAINT IF EXISTS property_authoring_reference_chk,
    DROP CONSTRAINT IF EXISTS property_authoring_surface_chk,
    DROP CONSTRAINT IF EXISTS property_authoring_rooms_chk,
    DROP CONSTRAINT IF EXISTS property_authoring_bathrooms_chk,
    DROP CONSTRAINT IF EXISTS property_authoring_construction_year_chk,
    DROP CONSTRAINT IF EXISTS property_authoring_address_line_chk;

ALTER TABLE real_estate_catalog_authoring.property_authoring
    ADD CONSTRAINT property_authoring_reference_chk CHECK (property_reference IS NULL OR property_reference ~ '^[A-Z0-9][A-Z0-9._/-]{3,63}$'),
    ADD CONSTRAINT property_authoring_surface_chk CHECK (surface_square_meters IS NULL OR surface_square_meters BETWEEN 1 AND 10000000),
    ADD CONSTRAINT property_authoring_rooms_chk CHECK (rooms IS NULL OR rooms BETWEEN 0 AND 1000),
    ADD CONSTRAINT property_authoring_bathrooms_chk CHECK (bathrooms IS NULL OR bathrooms BETWEEN 0 AND 1000),
    ADD CONSTRAINT property_authoring_construction_year_chk CHECK (construction_year IS NULL OR construction_year BETWEEN 1800 AND 9999),
    ADD CONSTRAINT property_authoring_address_line_chk CHECK (address_line IS NULL OR char_length(address_line) BETWEEN 3 AND 255);
