# Professional Status Runtime Health Extension Matrix

| Capacité | Contrat inspecté | Vérifications | Appels interdits |
|---|---|---|---|
| `ProfessionalStatusWorkflow` | `ProfessionalStatusWorkflow` | binding, type, constructibilité | `decide()` |
| `ProfessionalStatusWorkflowStore` | `ProfessionalStatusWorkflowStore` | binding, type, constructibilité | `initialize()`, `read()`, `append()` |

La décision est d'étendre Runtime Health de 35 à 37 capacités. L'inspection demeure structurelle : aucune requête SQL, écriture ou transaction n'est exécutée.
