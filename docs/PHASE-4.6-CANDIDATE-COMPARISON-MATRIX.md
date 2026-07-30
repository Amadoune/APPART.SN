# Phase 4.6 — Candidate Comparison Matrix

Notation : 1 faible, 5 fort. Pour le risque, 5 signifie un risque élevé.

| Capacité | Valeur | Maturité Domain | PostgreSQL | Autonomie | Risque transactionnel | Préparation globale |
|---|---:|---:|---:|---:|---:|---:|
| Media Item Lifecycle | 4 | 5 | 5 | 4 | 3 | **21/25** |
| Account Status Lifecycle | 5 | 5 | 1 | 3 | 4 | 18/25 |
| Moderation Case Lifecycle | 4 | 5 | 1 | 2 | 5 | 15/25 |
| Payment Lifecycle | 5 | 4 | 1 | 1 | 5 | 14/25 |
| Geographic Place Lifecycle | 3 | 4 | 2 | 2 | 4 | 15/25 |

La sélection ne repose pas sur le score seul : la disponibilité d'une source PostgreSQL propriétaire et l'absence de dépendance externe obligatoire rendent `Media Item Lifecycle` exploitable par étapes certifiables.
