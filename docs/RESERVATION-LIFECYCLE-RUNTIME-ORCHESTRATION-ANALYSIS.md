# Reservation Lifecycle Runtime Orchestration Analysis

`ReservationLifecycleEventOrchestrator` est le port demandé par 4.3D. Malgré son nom anticipant la suite de la roadmap, il ne produit, ne transporte et ne publie aucun événement dans ce sprint.

`DeterministicReservationLifecycleEventOrchestrator` coordonne exclusivement :

1. `ReservationLifecycleWorkflowStore::read` ;
2. contrôle de la version attendue ;
3. `ReservationLifecycleWorkflow::decide` ;
4. `ReservationLifecycleWorkflowStore::append` avec `expectedVersion + 1`.

Il ne construit aucune transition et ne possède aucune matrice métier. Les diagnostics refusés proviennent sans modification du workflow 4.3A. Les résultats de persistance 4.3B sont classés dans le résultat fermé 4.3D.

Une exception technique ou une incohérence entre une décision autorisée et le store devient `PersistenceCorrupted`, sans exposer de message ou de type d'infrastructure.
