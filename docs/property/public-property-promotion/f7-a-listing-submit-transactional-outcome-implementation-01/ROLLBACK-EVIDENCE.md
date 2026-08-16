# Preuves de rollback

## Unit

Le test paramétré couvre sept outcomes :

- Applied : commit ;
- AlreadyApplied : commit ;
- Denied : rollback et `LifecycleConflict` conservé ;
- ConcurrencyConflict : rollback et `ConcurrentModification` conservé ;
- PersistenceFailure : rollback et `DependencyUnavailable` conservé ;
- Applied sans transition : rollback fermé ;
- exception technique : rollback et réduction technique.

## PostgreSQL

Le test productif exécute le véritable Workflow, l’outbox, la delivery et l’ingestion PublicationReview, puis transforme le succès en closed failure avant le verdict externe. Après rollback :

- Aggregate Listing : Draft ;
- seule transition Workflow initiale : présente ;
- public-facts candidate : absente ;
- message/delivery Submitted : absents ;
- queue PublicationReview : absente ;
- Property F6 : présente ;
- ledger Promotion : présent.

Cette preuve démontre la frontière complète, pas seulement le statut Aggregate.
