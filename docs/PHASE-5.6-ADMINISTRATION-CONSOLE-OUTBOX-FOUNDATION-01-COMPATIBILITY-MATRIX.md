# Administration Console Outbox Foundation — Compatibility Matrix

| Baseline | Compatibilité |
|---|---|
| Discovery / Blueprint | Owner `AdministrationConsole` conservé |
| Contracts Foundation | Inchangée ; aucun contrat public modifié |
| Persistence Foundation | Migration 082 inchangée et séparée de 083 |
| Runtime Foundation | Aucun accès ni donnée Runtime |
| Owner Reader Foundations | Aucun accès direct |
| HTTP Foundation | Aucun accès ; fermée et inchangée |
| Event Foundation | Composants inchangés ; type propagé via Delivery |
| Delivery Foundation | Trois Deliveries V1, seules sources Outbox |
| Migration 082 | Empreintes SHA-256 conservées |
| Migration 083 | Additive, owner-scoped, avec rollback dédié |
| Foundation ultérieure | Aucune ouverte |

Le Repository constitue l'unique enclave PostgreSQL de cette Foundation.
