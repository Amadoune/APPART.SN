# Historical Account — PostgreSQL Repository Mapping Matrix

## Tables

| Snapshot V1 | Table | Clé |
|---|---|---|
| racine Account | `identity_access.accounts` | `account_id` |
| Credential | `identity_access.account_credentials` | `account_id` |
| Verification | `identity_access.account_verifications` | `(account_id, channel)` |
| RoleAssignment | `identity_access.account_role_assignments` | `(account_id, ordinal)` |
| Consent | `identity_access.account_consents` | `(account_id, ordinal)` |

## Racine

| Snapshot | Colonne |
|---|---|
| accountId | account_id UUID |
| email | email, unique |
| phone | phone, unique |
| name | person_name |
| lastChangedAt | last_changed_at + last_changed_at_offset |
| historicalSuspended | historical_suspended |
| historicalVersion | historical_version |
| snapshotVersion | snapshot_version = 1 |

Les offsets sont des métadonnées techniques nécessaires au round-trip fidèle
des `DateTimeImmutable`; ils ne constituent aucune donnée métier nouvelle.

## Enfants

- Credential : hash encodé reconstructible et changedAt;
- Verification : channel, token, issuedAt, expiresAt, verifiedAt;
- RoleAssignment : ordinal, roleId, grantedAt, revokedAt;
- Consent : ordinal, purpose, grantedAt, withdrawnAt.

Chaque date conserve son instant PostgreSQL et son offset d'origine.

## Lecture

```text
racine absente → null
racine présente + snapshot complet → Account
racine présente + enfant absent/invalide → CorruptedHistoricalAccountPersistence
```

La lecture reconstruit Snapshot V1 puis utilise exclusivement
`AccountPersistenceMapper`.

## Écriture

`add()` insère racine et enfants dans une transaction unique. `save()` effectue
d'abord :

```sql
UPDATE identity_access.accounts
...
WHERE account_id = :account_id
  AND historical_version = :expected_version
```

Une cardinalité différente de 1 produit `ConcurrentAccountModification`.
Les enfants sont ensuite remplacés atomiquement dans l'ordre certifié.

## Unicités

| Contrainte PostgreSQL | Exception |
|---|---|
| accounts PK | `DuplicateAccountIdentity::accountId()` |
| historical_accounts_email_uq | `DuplicateAccountIdentity::email()` |
| historical_accounts_phone_uq | `DuplicateAccountIdentity::phone()` |

PostgreSQL est l'arbitre interprocessus.
