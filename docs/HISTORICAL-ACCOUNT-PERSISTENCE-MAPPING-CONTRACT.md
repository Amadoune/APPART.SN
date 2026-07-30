# Historical Account — Persistence Mapping Contract

## Mapper pur

`AccountPersistenceMapper`, situé dans l'Infrastructure de persistance du
module, expose exactement :

```text
snapshot(Account)
→ HistoricalAccountPersistenceSnapshotV1
```

```text
account(HistoricalAccountPersistenceSnapshotV1)
→ Account
```

Il dépend uniquement du domaine IdentityAccess. Il transforme d'abord l'état
interne borné exposé par l'agrégat en Snapshot V1. Il ne connaît ni lignes SQL,
ni PDO, ni transactions, ni container Laravel.

## Mapping racine

| Account | Snapshot V1 |
|---|---|
| `id()` | accountId |
| `email()` | email |
| `phone()` | phone |
| `name()` | name |
| `persistenceState().lastChangedAt` | lastChangedAt |
| `isSuspended()` | historicalSuspended |
| `version()` | historicalVersion |
| état Credential interne | credential |
| états Verification email/phone | emailVerification / phoneVerification |
| états RoleAssignment dans l'ordre | roleAssignments + ordinal |
| états Consent dans l'ordre | consents + ordinal |

## Reconstitution

Les Value Objects revalident les identités et formats. Les secrets sont révélés
uniquement le temps de reconstruire `PasswordHash` et `VerificationToken`.
Les enfants sont reconstruits par factories non événementielles puis fournis à
`Account::reconstitute()`.

Le mapper ne recalcule ni statut, ni version, ni dates, ni ordinal. Une
divergence est rejetée.

## AccountRegistry

`AccountRegistry::find/add/save` reste inchangé. Le futur Repository traduira :

- snapshot durable valide vers `Account`;
- absence attestée vers `null`;
- unicité vers `DuplicateAccountIdentity`;
- conflit optimiste vers `ConcurrentAccountModification`.

Les erreurs de snapshot ne deviennent jamais `Missing`.

## Équivalence round-trip

Le contrat d'équivalence porte sur toutes les données persistables, les
historiques, les dates, les secrets reconstructibles et la version. Les
événements en attente ne sont volontairement pas inclus.
