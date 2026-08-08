# Administration Console Contracts V1 — Specification

Owner unique : `AdministrationConsole`.

Les contrats publics read-only sont `AdministrationOperatorReaderV1`,
`AdministrationQueueReaderV1` et `AdministrationAuditReaderV1`. Chaque lecture
consomme une `AdministrationSubjectKey` opaque et un `AdministrationObservedAt`
explicite, puis retourne exclusivement un statut V1.

Aucune identité interne, PII, décision d'un domaine source, projection,
Persistence ou diagnostic technique n'est exposé.
