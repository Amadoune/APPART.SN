# Lead Lifecycle Runtime Composition Analysis

## Décision

La capacité Lead Lifecycle est composée dans l'unique racine Laravel existante. Aucun Provider parallèle n'est créé.

Le graphe de production est :

```text
LeadLifecycleWorkflow

LeadLifecycleWorkflowStore
→ PostgreSqlLeadLifecycleWorkflowRepository
  → PDO PostgreSQL Runtime existant
  → LeadLifecycleWorkflowMapper
```

Tous les nœuds propres à la capacité sont des singletons paresseux. Le store est un alias de l'unique repository concret : l'alias ne construit donc aucune seconde instance.

Le bootstrap enregistre seulement des définitions. Il n'appelle ni workflow, ni store, n'ouvre aucune transaction et n'exécute aucune requête.
