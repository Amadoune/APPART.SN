# Phase 5.1B — User Profile Contract

## 1. Owner

Owner unique : `IdentityAccess / User Profile`.

UserProfile devient, après enrôlement/cutover certifié, l'autorité 5.x pour le
nom, l'email et le téléphone canoniques. Account historique reste inchangé.

## 2. État V1

```text
Unenrolled → Active
Active → Active (révision)
```

Closure ne supprime pas Profile ; elle contrôle son accessibilité via
Availability et la rétention.

## 3. Commands

- `EnrollProfile(intent, Account seed evidence, enrolledAt)`
- `ChangeDisplayName(intent, AccountId, name, expectedVersion, actor, at)`
- `ApplyVerifiedContactChange(intent, changeId, claimActivationEvidence,
  expectedProfileVersion, at)`

Email/téléphone ne sont jamais modifiés par une command Profile directe.

## 4. Queries

- `GetOwnProfile(AccountId)`
- `ResolveCanonicalContact(AccountId, authorizedPurpose)`
- `InspectProfileVersion(AccountId)`

Résultats Query : `Found`, `NotEnrolled`, `Unavailable`, `NotAuthorized`,
`Indeterminate`.

## 5. Résultats Commands

`Enrolled`, `Changed`, `AlreadyApplied`, `NotFound`, `NotEnrolled`,
`InvalidValue`, `AccountUnavailable`, `VersionConflict`, `EvidenceRejected`,
`ReplayConflict`, `Indeterminate`.

## 6. Invariants

1. exactement un Profile par AccountId ;
2. seed exact et idempotent depuis Snapshot V1 ;
3. version monotone ;
4. toute mutation produit exactement une ProfileRevision ;
5. contact actif possède une claim Active correspondante ;
6. changement de contact exige PendingContactChange Verified ;
7. ancienne valeur n'est jamais réutilisée comme preuve ;
8. Profile ne modifie jamais Account/Snapshot ;
9. Closed interdit les mutations ;
10. aucune ancienne PII dans les events.

## 7. Concurrence et idempotence

Expected version obligatoire. Le même intent/checksum est stable ; même intent
divergent est ReplayConflict. Activation contact et swap de claim doivent être
atomiques avec la révision Profile.

## 8. Versionnement

`UserProfile Contract V1`. La normalisation des valeurs est référencée par
`normalizationVersion`; elle ne change pas silencieusement.

## 9. Confidentialité

Queries exigent un purpose et une authorization. Les résultats masquent les
contacts selon usage. Events ne contiennent aucune valeur de nom/email/phone.

## 10. Compatibilité

Snapshot V1 est seed read-only. AccountRegistry n'est ni étendu ni remplacé.
Le cutover sera certifié en 5.1D.
