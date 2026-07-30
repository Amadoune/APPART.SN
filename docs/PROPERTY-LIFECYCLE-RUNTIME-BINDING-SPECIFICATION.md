# Property Lifecycle Runtime Binding Specification

Le provider Runtime existant déclare exactement :

- un singleton `PropertyLifecycleWorkflow` ;
- un singleton `PropertyLifecycleWorkflowMapper` ;
- un singleton `PostgreSqlPropertyLifecycleWorkflowRepository` ;
- un alias du repository vers `PropertyLifecycleWorkflowStore`.

Le repository reçoit la même instance PDO PostgreSQL que les autres adaptateurs certifiés et l'unique instance du mapper. L'alias et la classe concrète doivent toujours retourner la même instance.

Aucune fabrique métier, implémentation de test, Null Object ou résolution anticipée n'appartient au graphe de production.
