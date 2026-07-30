# Reservation Lifecycle Atomic Transaction Specification

La transaction certifiée couvre exactement :

1. lecture et contrôle de version par l'orchestrateur 4.3D ;
2. décision par le workflow 4.3A ;
3. append du journal 4.3B ;
4. création de l'événement par le catalogue 4.3E ;
5. création du payload Delivery 4.3F ;
6. création du message par le catalogue générique ;
7. append dans l'owner Outbox `reservation_lifecycle` ;
8. commit unique.

`PostgreSqlAggregateOutboxTransaction` implémente le nouveau port `ReservationLifecycleAtomicTransaction` sans introduire une seconde stratégie. Une transaction imbriquée reste interdite.

Seuls `Applied` et `AlreadyApplied` autorisent l'étape événementielle. `PersistenceCorrupted` déclenche un rollback. Les autres résultats fermés sont retournés sans écriture événementielle.
