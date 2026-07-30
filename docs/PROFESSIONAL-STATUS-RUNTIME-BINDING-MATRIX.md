# Professional Status Runtime Binding Matrix

| Contrat résolu | Implémentation | Cycle de vie | Instance partagée | Effet au bootstrap |
|---|---|---|---|---|
| `ProfessionalStatusWorkflow` | lui-même | singleton | oui | aucun |
| `ProfessionalStatusWorkflowMapper` | lui-même | singleton | oui | aucun |
| `PostgreSqlProfessionalStatusWorkflowRepository` | lui-même | singleton | oui | aucun |
| `ProfessionalStatusWorkflowStore` | alias du repository | singleton | oui, identité stricte | aucun |
| `PDO` | connexion `pgsql` Runtime | singleton existant | oui | aucune résolution par le graphe non sollicité |
