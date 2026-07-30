# Phase 5.1I — HTTP Runtime

## Statut

Jalon ouvert, implémenté et proposé à la certification. Le GO appartient à l'autorité.

L'adapter HTTP dépend uniquement du port `IdentityAccessHttpRuntime`. Il ne connaît ni les stores, ni PDO, ni les événements, ni l'Outbox. Le provider 5.1I installe un fallback de production fail-closed : une dépendance IAM absente produit `503 unavailable` et ne déclenche jamais une opération partielle.

Résultats publics fermés : `succeeded`, `accepted`, `authentication_failed`, `request_failed`, `forbidden`, `conflict`, `unavailable`.

Les diagnostics internes ne sont jamais sérialisés.
