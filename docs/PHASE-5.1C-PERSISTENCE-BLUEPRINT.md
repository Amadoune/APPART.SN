# Phase 5.1C — Persistence Blueprint

## 1. Autorité et périmètre

La persistence appartient exclusivement à `IdentityAccess Completion`. Elle
est indépendante de Laravel, HTTP, Runtime, Delivery et Outbox.

| Owner | Store/Repository attendu | Snapshot attendu |
|---|---|---|
| Authentication Attempts | AuthenticationAttemptStore | AuthenticationAttemptSnapshot |
| Sessions | SessionStore | SessionSnapshot + InvalidationCheckpointSnapshot |
| Password Recovery | RecoveryChallengeStore | RecoveryChallengeSnapshot |
| User Profile | UserProfileStore | UserProfileSnapshot |
| Identity Claims | IdentityClaimRegistry | IdentityClaimSnapshot |
| Pending Contact Changes | PendingContactChangeStore | PendingContactChangeSnapshot |
| Profile Revisions | ProfileRevisionStore | ProfileRevisionSnapshot |
| Account Closure | AccountClosureStore | AccountClosureSnapshot |

Account Availability ne possède ni table, ni mapper, ni repository.

## 2. Règles communes sans abstraction générique

Chaque owner possède ses propres types, mapper, SQL et tests. Aucune base
Repository générique n'est autorisée.

Tous les stores garantissent :

- snapshot détaché et immutable ;
- mapping explicite colonne par colonne ;
- version entière monotone ;
- `expectedVersion` sur mutation ;
- `intentId` + checksum pour idempotence ;
- transaction locale ou participation explicite à une transaction externe ;
- rollback intégral ;
- timestamps `timestamptz` avec offset conservé lorsque métier ;
- corruption mappée vers un résultat fermé, jamais un Aggregate partiel ;
- aucune dépendance à Account Status persistence.

## 3. Authentication Attempts

Clé owner : fingerprint HMAC versionné, jamais identifiant clair.

Champs :

- attempt key, policy version ;
- failure count et window start ;
- locked until nullable ;
- version ;
- last intent/checksum/outcome ;
- updatedAt.

Contraintes : compteur positif, lock futur cohérent, un seul record par
attempt key. Le mapper refuse une policy inconnue ou un checksum invalide.

Rétention : purge après fin de fenêtre + durée sécurité versionnée.

## 4. Sessions

Champs Session :

- session id UUID, AccountId logique ;
- secret hash, jamais secret ;
- state, version ;
- issuedAt, lastSeenAt, idleExpiresAt, absoluteExpiresAt ;
- rotatedTo nullable ;
- device fingerprint/label minimisés ;
- issued checkpoint ;
- last intent/checksum.

Table checkpoint propriétaire :

- AccountId logique unique ;
- monotonic checkpoint ;
- invalidatedAt/cause ;
- version/intent/checksum.

Une session est invalide si son checkpoint émis est antérieur au checkpoint
Account courant, même si son row reste Active.

Rétention : sessions terminales purgées après délai sécurité ; checkpoints
conservés tant qu'une session antérieure peut être présentée.

## 5. Password Recovery

Champs :

- recovery id UUID et AccountId logique ;
- challenge hash ;
- state/version ;
- issuedAt/expiresAt/consumedAt/revokedAt ;
- policy version ;
- last intent/checksum.

Index unique partiel : au plus un challenge `issued` par Account selon policy
V1. Le mapper vérifie toutes les chronologies.

Rétention : secret hash supprimable après terminal ; preuve structurelle
minimisée conservée selon security policy.

## 6. User Profile

Champs :

- AccountId logique PK ;
- encrypted/encoded display name, email, phone ;
- fingerprints contacts ;
- normalization version ;
- profile version ;
- enrolledAt/updatedAt ;
- last intent/checksum.

Le mapper ne lit jamais les tables 042. L'enrôlement 5.1D fournit un snapshot
seed explicite. Aucun trigger ou double-write Historical Account.

Rétention : Profile fermé reste présent et inaccessible ; Erasure exclu.

## 7. Identity Claims

Champs :

- claim id UUID ;
- AccountId logique ;
- type email/phone ;
- encrypted normalized value + fingerprint HMAC ;
- normalization version ;
- state/version ;
- reserved/activated/terminal timestamps ;
- contact change id nullable ;
- last intent/checksum.

Contrainte atomique : unique `(claim_type, claim_fingerprint)` pour tous les
états réservants. V1 ne réattribue jamais une ancienne claim : Superseded reste
dans l'index unique.

## 8. Pending Contact Changes

Champs :

- change id UUID, AccountId logique, contact type ;
- target encrypted value/fingerprint ;
- challenge hash ;
- claim id ;
- state/version ;
- requested/expires/verified/activated/terminal timestamps ;
- fresh-auth evidence id non secret ;
- policy/normalization versions ;
- last intent/checksum.

Index partiel : au plus un Pending/Verified par Account/type. Aucune FK vers
Profile/Claim : cohérence assurée par orchestration et transactions owner
explicitement composées en 5.1F.

## 9. Profile Revisions

Journal append-only :

- revision id UUID PK ;
- AccountId, profile result version unique par Account ;
- revision type, actor id ;
- occurredAt, source intent/change id ;
- protected old/new value references nullable ;
- policy/normalization versions ;
- checksum.

UPDATE/DELETE interdits au Repository. La purge éventuelle des blobs PII
protégés ne supprime pas la preuve structurelle.

## 10. Account Closure

Champs :

- AccountId logique PK ;
- state/version ;
- requested/closed/reopened timestamps ;
- actor id, reason category, policy version ;
- cooling-off deadline ;
- retention class et legal-hold bool ;
- session invalidation checkpoint nullable ;
- last intent/checksum.

Closed ne modifie aucune table Account/Status. Deleted/Anonymized absents des
contraintes.

## 11. Résultats persistence fermés

Chaque port expose ses propres résultats, alignés sur :

`Found`, `NotFound`, `Applied`, `AlreadyApplied`, `VersionConflict`,
`IdentityConflict`, `StateConflict`, `Corrupted`, `Rejected`.

Un PDOException n'est jamais une erreur métier publique ; les codes PostgreSQL
attendus sont mappés, les autres sont rollbackés et remontés comme défaut
technique.
