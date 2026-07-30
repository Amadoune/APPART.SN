# Phase 4.7 — Candidate Comparison Matrix

Notation : 1 faible, 5 fort. Pour le risque, 5 signifie un risque élevé.

| Capacité | Valeur | Maturité Domain | Source PostgreSQL | Autonomie | Risque transactionnel | Préparation |
|---|---:|---:|---:|---:|---:|---:|
| Administrative Action Lifecycle | 5 | 5 | 5 | 4 | 4 | **23/25** |
| Account Status Lifecycle | 5 | 5 | 1 | 3 | 4 | 18/25 |
| Moderation Case Lifecycle | 4 | 5 | 1 | 2 | 5 | 15/25 |
| Payment Lifecycle | 5 | 4 | 1 | 1 | 5 | 14/25 |
| Geographic Place Lifecycle | 3 | 4 | 2 | 2 | 4 | 15/25 |

Le score de préparation additionne valeur, maturité, source et autonomie, puis favorise un risque maîtrisable. Le choix final ne repose pas sur le score seul : `AdministrationAudit` possède déjà les données normatives, tandis que les préconditions du cycle peuvent être isolées dans des contrats explicites sans appeler un autre bounded context.
