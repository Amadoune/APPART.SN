# Phase 4.9C — Account Status Persistence Foundation

## Objet

La fondation rend durable le `Account Status Lifecycle` sans modifier
`Account`, `AccountRegistry` ou le Workflow 4.9B.

## Autorité

La table append-only
`identity_access.account_status_lifecycle_transitions` est l'unique source
autoritaire du statut courant et de sa version lifecycle après amorçage.

`AccountRegistry` est consulté exclusivement pour :

- qualifier l'existence historique;
- lire `isSuspended()` lors de l'amorçage;
- conserver la version Account comme provenance.

Le store n'appelle jamais `save`, `suspend` ou `reactivate`.

## Port

`AccountStatusWorkflowStore` expose uniquement :

```text
read(accountId)
bootstrap(accountId)
append(transition, contextV1)
```

Résultats de lecture :

```text
Found
AccountMissing
LegacyUninitialized
PersistenceRejected
```

Résultats d'écriture :

```text
Applied
AccountMissing
VersionConflict
PersistenceRejected
```

## Amorçage

Sous transaction et verrou advisory par `accountId` :

```text
compte absent
→ AccountMissing

compte présent + journal absent
→ snapshot Active/Suspended
→ version lifecycle 0
→ provenance legacy_account_version
→ Applied

journal valide déjà présent
→ Applied idempotent

journal présent sans compte ou corrompu
→ PersistenceRejected
```

L'amorçage ne produit aucun événement et ne modifie jamais l'agrégat.

## Append

L'append :

1. verrouille l'identité;
2. confirme l'existence historique;
3. exige un journal initialisé et intègre;
4. compare versions attendue et observée à la version lifecycle durable;
5. vérifie la cohérence transition/contexte;
6. ajoute exactement la version suivante.

La table refuse toute transition autre que :

```text
Active + Suspend → Suspended
Suspended + Reactivate → Active
```

## Atomicité

Le store ouvre une transaction uniquement s'il n'en existe pas. Dans une
transaction externe, il participe au commit ou rollback de l'appelant.

Une course d'amorçage converge vers une seule ligne version 0. Aucune écriture
ne vise l'agrégat historique.
