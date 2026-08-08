# Phase 5.8C — Experience & Acceptance — Runtime Compatibility Matrix

| Surface | État |
|---|---|
| ExperienceAcceptanceOwnerSource | Dépendance unique du Runtime |
| PostgreSQL et Mapper | Accessibles uniquement via composition Provider, jamais depuis Application Runtime |
| Diagnostics | runtimeId, version, availability uniquement |
| Contrats et Persistence certifiés | Inchangés |
| Migration 090 et rollback | Inchangés, empreintes protégées |
| Runtime Read, Owner Reader, HTTP | NON OUVERTS |
| Event, Delivery, Outbox | NON OUVERTS |
| Transport, Routing, Consumer | NON OUVERTS |

