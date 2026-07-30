# Phase 5.1F — Atomic Operations

Une opération exécute le travail de ses owners et l'enregistrement de son intent dans une même transaction PostgreSQL. Une transaction appelante est respectée au moyen d'un savepoint.

| Résultat | Effet |
|---|---|
| `Applied` | travail et intent validés ensemble |
| `Rejected` | refus métier stable et intent validé |
| `IdempotentReplay` | intent, compte, opération et checksum identiques ; callback non exécuté |
| `ReplayConflict` | intent réutilisé avec contexte divergent ; callback non exécuté |
| `RolledBack` | exception ; toutes les écritures et l'intent sont annulés |

Un advisory transaction lock sur `(accountId, operation)` sérialise les courses d'une même autorité. Le journal `identity_access_completion.atomic_operation_intents`, introduit par la migration additive 053, ne contient ni token, ni credential, ni PII, FK cross-domain ou cascade.

Les stores owner-scoped participent avec le même PDO. L'orchestrateur ne réalise aucune écriture SQL métier et ne contourne aucun store.
