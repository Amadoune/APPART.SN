# Professional Status Orchestration Runtime Binding Matrix

| Contrat | Implémentation | Cycle |
|---|---|---|
| `ProfessionalStatusContextualTransitionStore` | `PostgreSqlProfessionalStatusContextualTransitionRepository` | singleton / alias |
| `ProfessionalStatusContextualReplayInspector` | `PostgreSqlProfessionalStatusContextualReplayInspector` | singleton / alias |
| `ProfessionalStatusReplayPolicy` | elle-même | singleton |
| `ProfessionalStatusOrchestrator` | `DeterministicProfessionalStatusOrchestrator` | singleton / alias |

Le graphe réutilise le workflow, les mappers et le PDO Runtime existants. Aucun appel métier, SQL ou transactionnel n'est effectué au bootstrap.

Runtime Health expose uniquement `ProfessionalStatusOrchestrator`, portant le total de 37 à 38 capacités.
