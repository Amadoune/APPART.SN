# RC2 Stabilization — Iteration 05

## Périmètre

Cette itération corrige exclusivement la synchronisation `Submit public → Aggregate Listing Submitted`, en conservant la convergence Workflow, Outbox et Publication Review Queue.

## Première divergence

Le Submit public préparait les faits publics puis exécutait la transition événementielle du Workflow. Il n'appelait jamais l'autorité Aggregate `SubmitListing`.

Le résultat antérieur était donc : Workflow `submitted`, Aggregate `draft`.

## Correction unique

Le Submit public compose désormais :

1. la révision Submit déterministe via `ListingRevisionAllocatorV1` ;
2. l'évidence `SubmissionConfirmed`, owner-scoped ;
3. `SubmitListing` sur l'Aggregate ;
4. la transition événementielle Workflow, Outbox et Queue.

Ces opérations participent à une transaction locale englobante. `PostgreSqlAggregateOutboxTransaction` utilise un savepoint lorsqu'une transaction propriétaire existe déjà, ce qui conserve le rollback global sans transaction distribuée.

## Preuve autoritative

Listing réel : `39f3e66e-822a-4a61-98bb-e763eff21acd`.

| Modèle | État | Version |
|---|---|---:|
| Workflow | `submitted` | 2 |
| Aggregate Listing | `submitted` | 1 |

Le rejeu atteint ensuite Queue et Claim. La première divergence suivante est `BeginReview → HTTP 503`.

Conformément au fail-fast et à la règle d'une correction unique, cette nouvelle divergence n'est pas analysée ici. Approve, Projection et Search ne sont pas exécutés.

## Verdict

**NO GO PROPOSÉ — ITERATION 05**

La synchronisation Aggregate est corrigée et démontrée, mais les critères globaux de l'itération exigent également BeginReview et UnderReview, non atteints à cause de la nouvelle première divergence HTTP 503.
