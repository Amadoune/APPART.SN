# RC2 Stabilization — Iteration 09

## Périmètre

Cette itération traite exclusivement le replay strict de `ApprovePublicationV1`, depuis l'identité de commande HTTP jusqu'aux ledgers PublicationReview et Listing Publication Gateway.

Projection, Search et Public Listing ne sont pas qualifiés.

## Première divergence

ApprovePublication initial atteignait `Published`, mais le replay du même formulaire retournait HTTP 409.

`occurredAt` participe aux checksums canoniques de :

- `approve_and_publish` dans PublicationReview ;
- `approve_and_publish` dans Listing Publication Gateway ;
- l'activation idempotente associée à la composition HTTP.

Le formulaire Approve ne transportait pas cet instant. Le contrôleur en créait un nouveau à chaque POST : le même `commandId` atteignait donc le ledger avec un checksum différent et était correctement réduit en `DivergentCommand`.

## Correction unique

Le formulaire Approve fige désormais `occurredAt` avec `commandId` et `projectionCommandId`. La Request exige son format canonique et le contrôleur transmet exactement cette valeur.

Les checksums, les ledgers, l'optimistic locking et les transitions métier restent inchangés.

## Rejeu

Listing réel : `add18bba-6635-4bda-aba9-e66d7bf4084e`.

| Contrôle | Résultat |
|---|---|
| ApprovePublication initial | HTTP 200 |
| Workflow / Aggregate | `Published` |
| Replay du même POST | HTTP 200 |
| Réduction | `AlreadyApplied` |

Le replay utilise strictement les mêmes `commandId`, `projectionCommandId`, `expectedVersion`, acteur dérivé de la session et `occurredAt`.

La campagne s'arrête immédiatement après cette preuve.

## Verdict

**GO PROPOSÉ — ITERATION 09**

Le replay strict d'ApprovePublication est démontré sans régression et sans seconde correction.
