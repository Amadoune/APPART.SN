CREATE SCHEMA IF NOT EXISTS real_estate_catalog;

CREATE TABLE IF NOT EXISTS real_estate_catalog.properties (
    id uuid PRIMARY KEY,
    reference varchar(64) NOT NULL,
    type varchar(32) NOT NULL,
    surface integer NULL,
    rooms integer NOT NULL,
    bathrooms integer NOT NULL,
    construction_year integer NULL,
    status varchar(16) NOT NULL,
    last_changed_at timestamptz(6) NOT NULL,
    last_changed_at_offset smallint NOT NULL,
    version integer NOT NULL,
    CONSTRAINT properties_type_chk CHECK (type IN ('apartment', 'house', 'villa', 'land', 'office', 'commercial', 'other')),
    CONSTRAINT properties_status_chk CHECK (status IN ('active', 'archived')),
    CONSTRAINT properties_surface_chk CHECK (surface IS NULL OR surface BETWEEN 1 AND 10000000),
    CONSTRAINT properties_rooms_chk CHECK (rooms BETWEEN 0 AND 1000),
    CONSTRAINT properties_bathrooms_chk CHECK (bathrooms BETWEEN 0 AND 1000),
    CONSTRAINT properties_construction_year_chk CHECK (construction_year IS NULL OR construction_year BETWEEN 1800 AND 9999),
    CONSTRAINT properties_version_chk CHECK (version >= 0)
);

CREATE TABLE IF NOT EXISTS real_estate_catalog.property_reference_reservations (
    reference varchar(64) PRIMARY KEY,
    property_id uuid NOT NULL UNIQUE,
    CONSTRAINT property_reference_reservations_property_fk FOREIGN KEY (property_id) REFERENCES real_estate_catalog.properties (id) ON DELETE RESTRICT
);

CREATE TABLE IF NOT EXISTS real_estate_catalog.property_addresses (
    property_id uuid PRIMARY KEY,
    address_id uuid NOT NULL,
    geographic_place_id varchar(128) NOT NULL,
    address_line varchar(255) NOT NULL,
    CONSTRAINT property_addresses_property_fk FOREIGN KEY (property_id) REFERENCES real_estate_catalog.properties (id) ON DELETE RESTRICT,
    CONSTRAINT property_addresses_local_id_uq UNIQUE (property_id, address_id)
);

CREATE INDEX IF NOT EXISTS property_addresses_place_idx ON real_estate_catalog.property_addresses (geographic_place_id);
