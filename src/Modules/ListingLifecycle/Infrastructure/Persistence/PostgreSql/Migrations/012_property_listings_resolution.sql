CREATE INDEX IF NOT EXISTS listings_property_id_id_idx
    ON listing_lifecycle.listings (property_id, id);
