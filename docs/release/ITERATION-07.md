# RC2 Stabilization — Iteration 07

## Périmètre

Cette itération traite exclusivement le replay strict de `BeginPublicationReviewV1`, de l'identité de commande HTTP jusqu'au `PublicationReviewCommandLedger`.

Approve, Projection, Search et Public Listing ne sont pas exécutés.

## Première divergence

Le BeginReview initial atteignait correctement `UnderReview`, mais son replay retournait HTTP 409.

`occurredAt` participe au checksum canonique de la commande dans PublicationReview et dans la Gateway. Le formulaire BeginReview ne le transportait pas : le contrôleur créait un nouvel instant à chaque POST. Un même `commandId` arrivait donc au ledger avec deux checksums différents et était correctement réduit en `DivergentCommand`.

## Correction unique

Le formulaire BeginReview fige désormais `occurredAt` avec l'identité de commande. La Request exige son format canonique et le contrôleur transmet exactement cette valeur.

Le ledger, le checksum, l'optimistic locking, les commandes métier et les règles de transition restent inchangés.

## Rejeu terminal

Listing réel : `7ec63dab-dd11-4b2c-84a2-3ba6eb96de46`.

| Contrôle | Résultat |
|---|---|
| BeginReview initial | HTTP 200 |
| Workflow / Aggregate | `UnderReview` |
| Replay du même POST | HTTP 200 |
| Réduction | `AlreadyApplied` |

Le rejeu a utilisé le même `commandId`, le même `expectedVersion`, le même `actor` dérivé de la session et le même `occurredAt`. La campagne s'arrête immédiatement après cette preuve, avant Approve.

## Verdict

**GO PROPOSÉ — ITERATION 07**

Le replay strict de BeginReview est démontré sans régression et sans seconde correction.
