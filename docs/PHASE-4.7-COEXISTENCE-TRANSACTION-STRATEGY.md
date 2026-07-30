# Phase 4.7B-R1 — Coexistence Transaction Strategy

La future transition utilisera une transaction PostgreSQL unique :

```text
lock(actionId)
→ read Lifecycle authority
→ append Lifecycle journal
→ update historical compatibility mirror
→ commit
```

Le verrou doit être déterministe par `AdministrativeActionId`. La transaction doit fonctionner en mode local et réutiliser une transaction externe. Aucun commit partiel, compensation ou retry implicite n'est autorisé.

Le repository historique actuel reste inchangé. La future fondation devra fournir une écriture additive coordonnée au niveau transactionnel sans appeler son gestionnaire de transaction imbriqué.
