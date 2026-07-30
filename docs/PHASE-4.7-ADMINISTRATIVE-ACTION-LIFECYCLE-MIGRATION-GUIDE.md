# Phase 4.7B — Migration 034 & Rollback Guide

Migration :

```text
034_administrative_action_lifecycle_workflow.sql
```

Rollback :

```text
034_administrative_action_lifecycle_workflow.down.sql
```

La migration crée uniquement la table Lifecycle et son index. Elle ne modifie ni les quatre tables historiques ni la migration `001_administrative_action.sql`.

Le rollback supprime uniquement le journal Lifecycle. Il préserve intégralement le registre, les approbations, décisions et entrées d'audit historiques.
