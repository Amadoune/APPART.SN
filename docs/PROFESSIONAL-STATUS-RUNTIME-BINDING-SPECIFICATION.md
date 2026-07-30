# Professional Status Runtime Binding Specification

La racine `PublicProjectionRuntimeServiceProvider` porte l'unique composition autorisée :

```text
ProfessionalStatusWorkflow                    → singleton
ProfessionalStatusWorkflowMapper              → singleton
PostgreSqlProfessionalStatusWorkflowRepository → singleton
ProfessionalStatusWorkflowStore               → alias du repository
PDO                                            → connexion PostgreSQL Runtime existante
```

Tous les bindings sont déclaratifs et paresseux. La résolution du contrat et celle de l'implémentation retournent la même instance. Aucun Fake, Null Object, fallback ou Provider parallèle n'est autorisé.
