# Lead Lifecycle Runtime Binding Specification

## Bindings certifiés

- `LeadLifecycleWorkflow` : singleton auto-construit ;
- `LeadLifecycleWorkflowMapper` : singleton auto-construit ;
- `PostgreSqlLeadLifecycleWorkflowRepository` : singleton auto-construit avec les deux dépendances certifiées ;
- `LeadLifecycleWorkflowStore` : alias exclusif du repository PostgreSQL.

Le `PDO` est le binding PostgreSQL Runtime préexistant. Aucun connecteur, transaction manager, Fake, Null Object ou fallback supplémentaire n'est introduit.

La résolution est réalisée exclusivement par le conteneur Laravel. La construction des objets ne déclenche aucune opération métier ou PostgreSQL.
