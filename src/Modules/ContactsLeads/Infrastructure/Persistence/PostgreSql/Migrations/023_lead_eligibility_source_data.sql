CREATE SCHEMA IF NOT EXISTS contacts_leads;

CREATE TABLE IF NOT EXISTS contacts_leads.lead_eligibility_decisions (
    listing_id uuid NOT NULL,
    version bigint NOT NULL CHECK (version > 0),
    normative_advertiser_id uuid NULL,
    listing_decision text NOT NULL CHECK (listing_decision IN ('contactable','missing','not_published','closed')),
    evaluated_advertiser_id uuid NOT NULL,
    advertiser_decision text NOT NULL CHECK (advertiser_decision IN ('eligible_recipient','missing','suspended','not_listing_recipient')),
    coherence_id uuid NOT NULL,
    effective_at timestamptz(6) NOT NULL,
    materialization_checksum char(64) NOT NULL CHECK (materialization_checksum ~ '^[0-9a-f]{64}$'),
    PRIMARY KEY (listing_id, version)
);

CREATE UNIQUE INDEX IF NOT EXISTS lead_eligibility_coherence_per_listing
    ON contacts_leads.lead_eligibility_decisions (listing_id, coherence_id);

CREATE INDEX IF NOT EXISTS lead_eligibility_current_lookup
    ON contacts_leads.lead_eligibility_decisions (listing_id, version DESC)
    INCLUDE (normative_advertiser_id, listing_decision, evaluated_advertiser_id, advertiser_decision, coherence_id, effective_at, materialization_checksum);
