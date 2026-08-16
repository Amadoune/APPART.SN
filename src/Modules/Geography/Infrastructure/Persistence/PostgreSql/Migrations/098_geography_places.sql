CREATE SCHEMA IF NOT EXISTS geography;

CREATE TABLE IF NOT EXISTS geography.places (
    id uuid PRIMARY KEY,
    official_name varchar(120) NOT NULL,
    code varchar(32) NOT NULL,
    type varchar(32) NOT NULL CHECK (type IN ('country','region','department','city','district','neighborhood')),
    country_code char(2) NOT NULL,
    parent_place_id uuid NULL,
    latitude numeric(9,6) NULL CHECK (latitude BETWEEN -90 AND 90),
    longitude numeric(9,6) NULL CHECK (longitude BETWEEN -180 AND 180),
    enabled boolean NOT NULL,
    merged_into_place_id uuid NULL,
    aggregate_version integer NOT NULL CHECK (aggregate_version >= 1),
    CONSTRAINT places_country_code_code_uq UNIQUE (country_code, code),
    CONSTRAINT places_parent_not_self_ck CHECK (parent_place_id IS NULL OR parent_place_id <> id),
    CONSTRAINT places_merge_not_self_ck CHECK (merged_into_place_id IS NULL OR merged_into_place_id <> id),
    CONSTRAINT places_coordinates_pair_ck CHECK ((latitude IS NULL) = (longitude IS NULL)),
    CONSTRAINT places_merge_disabled_ck CHECK (merged_into_place_id IS NULL OR enabled = false),
    CONSTRAINT places_parent_fk FOREIGN KEY (parent_place_id) REFERENCES geography.places(id) ON DELETE RESTRICT,
    CONSTRAINT places_merge_target_fk FOREIGN KEY (merged_into_place_id) REFERENCES geography.places(id) ON DELETE RESTRICT
);

CREATE TABLE IF NOT EXISTS geography.place_aliases (
    place_id uuid NOT NULL,
    name varchar(120) NOT NULL,
    normalization_key varchar(120) NOT NULL,
    recorded_at timestamptz NOT NULL,
    PRIMARY KEY (place_id, normalization_key),
    CONSTRAINT place_aliases_place_fk FOREIGN KEY (place_id) REFERENCES geography.places(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS places_selection_idx ON geography.places(type, parent_place_id, enabled, merged_into_place_id, lower(official_name), id);
