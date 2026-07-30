# Transactional Runtime Composition — spécification

## Invariant Runtime

Toute mutation Aggregate produisant un message Public Projection s'exécute dans `PostgreSqlAggregateOutboxTransaction`. Le Repository producteur est résolu par Laravel avec `PostgreSqlAggregateOutboxParticipantTransaction`.

Le propriétaire transactionnel :

1. ouvre la transaction sur la PDO `pgsql` ;
2. exécute mutation et append Outbox ;
3. commit les deux écritures ou rollback les deux ;
4. refuse toute transaction propriétaire imbriquée.

Le participant exige une transaction active et n'exécute ni `beginTransaction()`, ni commit, ni rollback.

## Compatibilité

La logique historique des Repositories reste inchangée. Leurs contrats PostgreSQL, lectures, versions et protections de concurrence continuent d'être validés par les suites existantes. La racine Runtime impose simplement le participant certifié aux producteurs Public Projection.
