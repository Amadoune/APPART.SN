# Phase 5.3D — Persistence Foundation

## Statut

**Recommandation : GO PROPOSÉ**

La Persistence `ModerationReports` est strictement owner-local. Elle ne compose
aucun Runtime, HTTP, Event, Delivery, Outbox ou domaine externe.

## Autorités persistées

| Autorité | Port Application | Implémentation PostgreSQL |
|---|---|---|
| Dossier et historique | `ModerationCaseStore` | `PostgreSqlModerationCaseStore` |
| Décision courante/historique | `ModerationDecisionStore` | `PostgreSqlModerationDecisionStore` |
| Projection de file | `ModerationQueueStore` | `PostgreSqlModerationQueueStore` |

`ModerationDecisionStore` est read-only. L'écriture des décisions reste
atomiquement portée par `ModerationCaseStore`.

## États contractuels

- `ModerationCasePersistenceState` ;
- `ModerationPersistenceRecord` ;
- `ModerationQueueItemState` ;
- résultats de lecture fermés ;
- résultats d'écriture fermés :
  `Applied`, `AlreadyApplied`, `DivergentIntent`, `VersionConflict`,
  `IdentityConflict`, `Rejected`.

Les états Application n'exposent ni PDO, SQL, mapper, snapshot Infrastructure
ou type d'un autre module.

## Mapper

`ModerationPersistenceMapper` assure :

- conversion déterministe état/paramètres ;
- payload JSON object canonique ;
- tri récursif des clés d'objets ;
- checksum SHA-256 ;
- reconstitution sans décision métier ;
- préservation des timestamps, versions et identifiants opaques.

## Transactions

- transaction locale propriétaire ;
- participation sûre par savepoint ;
- advisory lock transactionnel par identité ;
- optimistic locking par `expectedVersion` ;
- intent journal vérifié avant la version ;
- rollback de l'état, des révisions et de l'intent en une unité ;
- aucune transaction distribuée.

## Append-only

Rapports, constats et décisions sont stockés sous forme de révisions
append-only indexées par `case_version`. Les supersessions et intents sont
permanents. Seul le snapshot racine et la projection de file sont mis à jour.

## Queue

La file est une projection locale :

- source version monotone ;
- résultats déterministes pour même version ;
- lease bornée ;
- reprise après expiration ;
- checkpoint monotone ;
- aucune autorité sur l'état métier du dossier.

## Hors périmètre

- six amendements 5.3C ;
- IAM, Listing, Media, Account, Professional et Administration Audit ;
- Runtime, Provider et bindings ;
- HTTP ;
- Event, transport, Delivery, Consumer et Outbox ;
- command handoff ;
- toute migration autre que 063.
