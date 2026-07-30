# Listing Publication Event Integration Analysis

## Périmètre

Le Sprint 4.1E relie les fondations certifiées sans introduire de nouvelle règle métier : l'orchestrateur 4.1D décide et persiste, le catalogue 4.1EA produit l'événement canonique, l'adaptateur 4.1EBA le transporte et l'Outbox certifiée l'enregistre.

## Décision

`AtomicListingPublicationEventOrchestrator` est un décorateur applicatif du port d'orchestration existant. Il reçoit la demande de transition et les métadonnées explicites, puis exécute l'orchestrateur et les écritures Outbox dans `ListingPublicationAtomicTransaction`.

L'implémentation PostgreSQL réutilise `PostgreSqlAggregateOutboxTransaction`. Il n'existe donc ni seconde transaction, ni nouvelle Outbox, ni coordination compensatoire.

## Propriétés garanties

- `Denied`, conflit ou échec de persistance : aucun événement n'est construit ni écrit.
- `Applied` et `AlreadyApplied` : la matrice certifiée produit l'événement attendu.
- toute écriture Outbox autre que `Applied` ou `AlreadyApplied` provoque le rollback de la transition ;
- un nouvel essai strictement identique converge par les contrôles d'idempotence existants ;
- `occurredAt` et `recordedAt` proviennent uniquement de la requête ;
- aucun appel au Worker, Consumer, routeur, HTTP ou Projection n'est ajouté.

## Frontières

Le workflow demeure propriétaire de la décision, le store de l'écriture métier, le catalogue du mapping transition/événement, l'adaptateur du transport et l'Outbox de la livraison durable. L'intégrateur ne réinterprète aucun diagnostic et ne reconstruit aucun événement.
