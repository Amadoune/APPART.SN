# Historical Account — Secure Snapshot Boundary V1

## Périmètre

La frontière appartient exclusivement à :

```text
IdentityAccess
→ Historical Account Persistence
```

Elle ne constitue ni une API HTTP, ni un DTO public, ni un contrat Event.

## Contrats

```text
Account::persistenceState()
→ état interne neutre et borné
→ AccountPersistenceMapper
→ HistoricalAccountPersistenceSnapshotV1
```

```text
AccountPersistenceMapper::account(Snapshot V1)
→ Account::reconstitute(...)
```

Le snapshot racine contient :

- accountId, email, téléphone et nom;
- lastChangedAt;
- suspended et version historiques;
- Credential;
- Verification Email et Phone;
- historique ordonné des RoleAssignment;
- historique ordonné des Consent;
- `snapshotVersion=1`.

Les événements en attente sont exclus.

## Secrets

Le hash encodé et les tokens sont transportés par
`SensitivePersistenceValueV1`. Ce type :

- n'implémente aucun `__toString()`;
- refuse `serialize()`;
- refuse la sérialisation JSON;
- masque sa valeur dans `__debugInfo()`;
- marque l'entrée comme `SensitiveParameter`;
- ne restitue la valeur que par `revealForPersistence()`.

Les snapshots Credential, Verification et Account refusent également la
sérialisation PHP générique. Leur debug masque les données sensibles.

Le reveal est borné par règle d'architecture au mapper de persistance situé
dans `IdentityAccess/Infrastructure/Persistence/HistoricalAccount`. Il ne
doit jamais être appelé par HTTP, logs, Events, projections ou Workflow.

## Hydratation

Les factories suivantes restaurent directement l'état, sans mutation métier :

- `Verification::reconstitute(...)`;
- `RoleAssignment::reconstitute(...)`;
- `Consent::reconstitute(...)`;
- `Account::reconstitute(...)`.

Le mapper n'appelle jamais `verify`, `grantRole`, `revokeRole`, `grantConsent`,
`withdrawConsent`, `suspend` ou `reactivate`. Une Account hydratée possède une
liste d'événements vide.

## Interdictions

- aucune réflexion;
- aucune closure liée à une portée privée;
- aucun `serialize()`/`unserialize()` de l'état;
- aucun setter;
- aucun PDO, SQL, Runtime ou transaction;
- aucune publication;
- aucune correction silencieuse.

Toute V2 exige un amendement versionné.

La séparation état interne / snapshot empêche le domaine de dépendre de
`Infrastructure`, conformément à la baseline Architecture.
