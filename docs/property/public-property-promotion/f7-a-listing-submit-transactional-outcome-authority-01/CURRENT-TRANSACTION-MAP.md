# Carte transactionnelle actuelle

## Ordre réel

1. Hors transaction Listing : lecture Draft/Ownership puis Promotion dans sa transaction RealEstateCatalog.
2. `PostgreSqlListingTransaction::run()` ouvre `BEGIN`.
3. `PostgreSqlAuthoringPublicFactHandoff::prepare()` participe via savepoint sur la même PDO.
4. `SubmitListing` relit l’Aggregate, applique la transition Domain, écrit `listing_lifecycle.listings` et `listing_revisions` via un savepoint participant.
5. `AtomicListingPublicationEventOrchestrator` ouvre un savepoint participant.
6. Le Workflow relit puis écrit `listing_lifecycle.publication_workflow_transitions`.
7. Sur succès Workflow, l’outbox locale et l’ingestion PublicationReview sont écrites.
8. Le résultat fermé remonte à la closure externe.
9. Actuellement, toute fin normale de closure provoque `COMMIT`, y compris un résultat fermé non réussi.

## Mutations dans la même transaction PostgreSQL

- `listing_lifecycle.authoring_public_fact_handoffs` ;
- `listing_lifecycle.listings` ;
- `listing_lifecycle.listing_revisions` ;
- `listing_lifecycle.publication_workflow_transitions` ;
- outbox publique du owner Listing ;
- deliveries correspondantes ;
- `publication_review.queue_items` pour les événements Submitted/Resubmitted.

La Promotion (`real_estate_catalog.properties` et ledger 100) est validée avant ce `BEGIN` et n’y participe pas.
