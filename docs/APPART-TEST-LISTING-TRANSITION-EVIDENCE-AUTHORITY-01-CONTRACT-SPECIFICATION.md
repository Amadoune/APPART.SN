# Listing Transition Evidence Authority 01 — Contract Specification

`ListingTransitionEvidenceFactoryV1` reste `NOT_MATERIALIZED`.

Une future surface devra recevoir un reason provenant explicitement du command owner concerné, puis composer mécaniquement ActorId, trigger, reason, origin et occurredAt. Elle ne devra résoudre ni révision, média, expiration ou état.

Deux sources de command resteront distinctes : authoring pour Submit et décision de modération pour Review/Publish.
