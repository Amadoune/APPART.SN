# Phase 5.1F — Rollback Policy

| Situation | Décision | Persistance |
|---|---|---|
| Succès de tous les owners | `Applied` | commit global + intent |
| Refus métier fermé | `Rejected` | état explicitement produit + intent |
| Exception avant ou pendant le journal | `RolledBack` | rollback de tous les owners |
| Replay identique | `IdempotentReplay` | aucune nouvelle écriture |
| Replay divergent | `ReplayConflict` | aucune nouvelle écriture |
| Transaction englobante | résultat fermé | release ou rollback au savepoint |

L'exception technique n'est jamais publiée par le contrat Application. Aucun état partiel ni intent fantôme ne peut être validé. L'unicité de `intent_id` constitue la dernière ligne de défense et le verrou est libéré avec la transaction. Les migrations 041 à 052 restent inchangées.
