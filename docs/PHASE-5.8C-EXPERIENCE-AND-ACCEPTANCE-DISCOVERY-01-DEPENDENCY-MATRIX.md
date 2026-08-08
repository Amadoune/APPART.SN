# Phase 5.8C — Experience & Acceptance — Dependency Matrix

| Dépendance candidate | Autorisation | Limite |
|---|---|---|
| Contrats publics V1 certifiés | Autorisée en lecture seule future | Aucun accès aux sources owner-scoped |
| Façades HTTP publiques certifiées | Autorisée pour scénarios E2E futurs | Aucun contournement Controller/Reader |
| SecurityCompliance | Preuves publiques minimales seulement | Aucun secret, PII ou audit détaillé |
| ReliabilityOperations | États publics de disponibilité/readiness seulement | Aucun accès Runtime, métrique brute ou Infrastructure |
| Décisions métier des autres owners | Référence de résultat attendu | Aucune réinterprétation |
| Design system candidat | À qualifier ultérieurement | Aucune implémentation dans ce jalon |

## Dépendances interdites

PostgreSQL, SQL, Mapper, Repository, OwnerSource, Runtime interne, migration, secret, PII, payload métier, Provider, Event, Delivery, Outbox, Transport, Routing et Consumer sont interdits.

