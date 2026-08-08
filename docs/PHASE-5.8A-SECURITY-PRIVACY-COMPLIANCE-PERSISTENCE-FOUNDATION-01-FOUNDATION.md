# SecurityCompliance Persistence Foundation

`SecurityCompliance` est l'owner unique. `SecurityComplianceOwnerSource` porte cinq streams indépendants : `SecretInventory`, `SecurityAudit`, `Incident`, `PrivacyPolicy` et `ComplianceControl`.

Chaque stream possède un `RevisionState`, un `ReadResult` et un `WriteResult`. Seul l'état public `Available` est journalisé ; `Missing`, `Corrupted` et `DependencyUnavailable` qualifient mécaniquement une lecture. Aucun secret, clé, PII, contenu d'audit, configuration ou donnée métier n'est persisté.

La source PostgreSQL utilise un journal append-only owner-scoped, un index courant dérivé, des révisions monotones, un checksum SHA-256 canonique, un verrou consultatif par sujet et stream, l'optimistic locking et des savepoints locaux.

La migration additive unique est `086_security_compliance_owner_source.sql`, avec son rollback. Les migrations 084–085 restent gelées et inchangées.
