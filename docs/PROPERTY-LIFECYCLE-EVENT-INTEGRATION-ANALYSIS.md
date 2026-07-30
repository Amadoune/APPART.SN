# Property Lifecycle Event Integration Analysis

## Décision

`AtomicPropertyLifecycleEventOrchestrator` décore le port 4.2D et coordonne uniquement les fondations certifiées 4.2E à 4.2H. Il reçoit les deux instants 4.2E explicitement et ne consulte aucune horloge.

La classe `PostgreSqlAggregateOutboxTransaction` existante implémente les ports atomiques Listing Publication et Property Lifecycle. Une seule connexion, une seule stratégie et une seule frontière transactionnelle sont ainsi conservées.

## Responsabilités

- déléguer la décision et la persistance à `PropertyLifecycleOrchestrator` ;
- arrêter immédiatement pour tout résultat autre que `Applied` ou `AlreadyApplied` ;
- demander l'événement au catalogue 4.2E ;
- utiliser le payload 4.2F et la fabrique Delivery existante ;
- écrire dans l'Outbox existante ;
- transformer toute défaillance technique en `PersistenceFailure / InfrastructureFailure`.

Aucune matrice, transition, identité événementielle ou décision métier n'est reconstruite.
