# Account Status — Decision Ownership Matrix

| Catégorie | Résultat | Owner |
|---|---|---|
| Métier | transition vers `Active` ou `Suspended` | Workflow |
| Métier | `AlreadyInState` | Workflow |
| Métier | `InvalidContext` | Workflow |
| Rejeu | `Found`, `Missing`, `Corrupted` | Inspection |
| Rejeu | `AlreadyApplied`, `ReplayConflict` | Inspection |
| Coordination | `InspectionCorrupted` | Orchestration |
| Coordination | ordre d'appel et court-circuit | Orchestration |
| Durable | `AccountMissing` | Persistance |
| Durable | `VersionConflict` | Persistance |
| Durable | `PersistenceRejected`, `Applied` | Persistance |
| Effet aval | rôles | Role Assignment |
| Effet aval | sessions | Authentication / Session |
| Donnée orthogonale | Credentials | Credential |
| Donnée orthogonale | Verification | Verification |
| Donnée orthogonale | Consent | Consent |

## Interdictions croisées

| Couche | Ne peut jamais |
|---|---|
| Workflow | lire durablement, inspecter le rejeu, révoquer rôles/sessions |
| Inspection | décider un statut, qualifier l'existence courante |
| Orchestration | recalculer transition, rejeu ou concurrence |
| Persistance | choisir l'état cible ou classer le rejeu |
