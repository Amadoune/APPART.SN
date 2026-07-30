# Historical Account — Transaction and Concurrency Blueprint

## Connexion

Le futur `PostgreSqlAccountRepository` reçoit une instance PDO PostgreSQL
injectée. Le mapper ne possède aucune connexion. La même instance doit pouvoir
être injectée dans le store lifecycle lors du bootstrap 4.9C.

## Frontière transactionnelle

Pour `add()` et `save()` :

1. si aucune transaction n'est active, le Repository ouvre, commit ou rollback;
2. si une transaction externe est active, il la rejoint;
3. un participant ne commit ni ne rollback la transaction externe;
4. toute écriture racine/enfant appartient à la même transaction.

`find()` effectue une lecture cohérente sans mutation. Dans une transaction
externe, il utilise son snapshot et sa connexion.

## Add

```text
BEGIN si owner
→ INSERT racine version 0
→ INSERT Credential
→ INSERT Verification Email puis Phone
→ INSERT RoleAssignment ordonnés
→ INSERT Consent ordonnés
→ COMMIT si owner
```

Les index uniques de `accountId`, email et téléphone sont l'arbitre
interprocessus. Les SQLSTATE d'unicité sont traduits exactement vers
`DuplicateAccountIdentity::accountId|email|phone`.

## Save optimiste

Préconditions contractuelles proposées :

```text
expectedVersion ≥ 0
candidate.version = expectedVersion + 1
```

Algorithme :

```text
BEGIN si owner
→ verrou consultatif transactionnel(accountId), ou verrou racine équivalent
→ UPDATE accounts
     SET ..., version=:candidateVersion
     WHERE account_id=:id AND version=:expectedVersion
→ cardinalité 0 : ConcurrentAccountModification
→ synchronisation atomique des enfants
→ COMMIT si owner
```

La vérification de version n'est pas une décision métier : elle applique le
port `AccountRegistry`. La base reste l'arbitre, jamais une lecture préalable
non verrouillée.

## Enfants et ordre

Ordre stable :

1. racine Account;
2. Credential;
3. Verification par channel;
4. RoleAssignment par ordinal;
5. Consent par ordinal.

Une stratégie replace-all des enfants est recevable seulement dans la même
transaction et si elle conserve exactement l'historique et les ordres. Une
stratégie différentielle est préférable pour l'audit, mais ne doit pas déplacer
les invariants dans SQL.

## Bootstrap partagé avec 4.9C

Ordre normatif pour éviter les deadlocks :

```text
transaction externe unique
→ verrou accountId commun
→ AccountRegistry::find()
→ lecture journal 041
→ bootstrap 041 si absent
→ commit par l'orchestrateur propriétaire
```

La recertification 4.9C devra aligner son verrou consultatif existant avec
l'ordre de la fondation Account. Les versions restent indépendantes.

## Isolation et garanties

- unicités protégées par PostgreSQL;
- concurrence optimiste protégée par update conditionnel;
- aucune fenêtre read-then-write non protégée;
- rollback intégral sur toute erreur;
- aucune transaction au bootstrap Laravel;
- garanties identiques entre processus et workers.

Le niveau d'isolation exact sera validé en 4.9P-C avec des tests à deux
connexions; `READ COMMITTED` avec verrouillage explicite est pressenti.

## Résultats

Le port existant ne retourne pas les enums pressentis :

- lecture : `Account|null`, corruption rejetée;
- add : `void` ou `DuplicateAccountIdentity`;
- save : `void` ou `ConcurrentAccountModification`.

La Persistance ne doit pas changer ce contrat sans amendement versionné.
