# Property Transaction Boundaries

| Opération | Frontière transactionnelle actuelle | Écritures coordonnées | Idempotence / rollback | Absence pertinente |
|---|---|---|---|---|
| Création/mutation Property Authoring | `PostgreSqlPropertyAuthoringStore::save` ouvre une transaction si nécessaire, advisory lock par propertyId | Une ligne Authoring | expectedVersion + intentId/checksum ; rollback local | Aucun Aggregate Property, événement ou outbox |
| Création Aggregate Property | `PostgreSqlPropertyRepository` avec `PropertyTransaction`; en composition runtime, participant Aggregate+Outbox requis | root Property, réservation de référence, adresse | contraintes uniques / optimistic locking ; rollback local | Aucun appel depuis Authoring |
| Property Lifecycle transition | `PostgreSqlAggregateOutboxTransaction` | workflow lifecycle + messages Public Projection Outbox | transaction locale ou savepoint ; message idempotent | Ne crée pas Aggregate et ne lit pas Authoring |
| Création Listing | `PostgreSqlListingTransaction` | Aggregate Listing, workflow, draft/ownership/portfolio selon composition | intent ledger, expected versions, savepoints | Référence le propertyId ; ne coordonne pas Property Aggregate |
| Submit Listing | `ListingCreationTransaction` externe avec savepoints Aggregate+Outbox | Public Fact candidat transactionKind, Aggregate Listing Submitted, workflow/événement ListingSubmitted | replay par intents/versions ; rollback global de la composition Listing | Aucune écriture Property |
| ApprovePublication | `PostgreSqlListingPublicationGatewayPersistence::run`, transaction locale/savepoint | Gateway ledger, Workflow/ Aggregate Listing, Public Facts, Listing events/outbox, Queue dans sa transaction propriétaire | optimistic locking, checksums, replay | Property n'est lu que comme disponibilité ; aucune promotion |
| Projection Activation | `PostgreSqlPublicationReviewQueue::activate` | lock queue/version, appel projection, ledger activation, version Queue si succès | command checksum, `AlreadyApplied`; rollback si échec | L'activation retourne NotReady avant écriture lorsque Property source manque |
| Projection Store | writer Public Projection | record current/historical/tombstone, generation, watermark | résultat fermé et idempotence par payload/watermark | Ne coordonne jamais une création Property |

## Coordination constatée

Les transactions sont locales à leurs owners. Il n'existe aucune transaction ou compensation coordonnant `PropertyAuthoringStore` et `PropertyRegistry`.

## Portée du Discovery

Ce document constate les limites actuelles. Il ne prescrit ni transaction distribuée, ni savepoint supplémentaire, ni outbox nouvelle.
