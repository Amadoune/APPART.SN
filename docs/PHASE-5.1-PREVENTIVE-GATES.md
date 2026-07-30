# Phase 5.1 — Preventive Gates

## G1 — Frozen Boundary

Test de diff et architecture interdisant toute modification de `Account`,
`AccountRegistry`, Snapshot V1, 041–043, Account Status, Runtime Health 58,
HTTP/Outbox Status.

## G2 — Security Decisions

Avant Contracts, décider :

- password verifier et absence d'exposition ;
- enumeration response/timing ;
- rate-limit/lockout keys et durée ;
- session token format, rotation, expiry et concurrence ;
- remember-me ;
- recovery/contact challenge entropy, TTL et usage unique.

## G3 — Profile Authority Cutover

Avant toute mutation Profile :

- seed idempotent de tous les Accounts ;
- claims normalisées uniques ;
- rapport de divergence nul ou quarantaine ;
- ordre de cutover explicite ;
- rollback sans double autorité.

## G4 — Closure Availability

Avant Runtime :

- precedence Closed > Suspended/Active pour l'accès ;
- Reopened ne réactive jamais Status ;
- session invalidation owner et checkpoint ;
- cross-domain commands/events sans écriture directe ;
- Erasure exclu.

## G5 — Persistence Ownership

Avant migrations :

- numéros disponibles après 043 ;
- schémas/tables owners uniques ;
- down/rollback ;
- optimistic locking, idempotence et concurrence réelle ;
- PII, chiffrement/hash et rétention ;
- aucune FK cross-domain ou cascade Historical Account.

## G6 — Runtime Isolation

Avant Provider :

- composition IAM Completion distincte ;
- aucun changement aux bindings gelés ;
- Runtime Health 58 inchangé ;
- health propre 5.1 si requis, sans ajout implicite au catalogue.

## G7 — Delivery Owner

Avant Outbox :

- event catalog minimal ;
- consumers réels ;
- owner physique distinct ;
- aucun changement 043/generic Outbox ;
- retry, quarantine, replay, privacy.

## G8 — HTTP Security

Avant routes :

- authn/authz et CSRF/cookies ;
- rate limits ;
- mapping non énumérant ;
- no-store et secret redaction ;
- ownership des routes/controllers séparé de Account Status ;
- Feature/security tests.

## G9 — Quality

Unit, Architecture, Feature, PostgreSQL 18.x, Runtime, PHPStan, Pint,
`git diff --check` et attribution du diff selon 5.0B.
