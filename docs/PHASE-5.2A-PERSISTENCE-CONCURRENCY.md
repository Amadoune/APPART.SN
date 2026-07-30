# Phase 5.2A — Persistence Concurrency

## Sérialisation

Les stores mutables prennent un verrou advisory transactionnel dérivé de leur
identité propriétaire avant lecture et décision. La décision compare ensuite
`expectedVersion`, `intentId` et checksum.

## Résultats fermés

- `Applied` : mutation nouvelle conforme ;
- `AlreadyApplied` : replay canonique du même intent ;
- `DivergentIntent` : même intent, contenu différent ;
- `VersionConflict` : version observée différente de la version attendue ;
- `IdentityConflict` : collision d’identité propriétaire ;
- `Rejected` : violation d’un invariant immuable.

PropertyAuthoring et ListingLifecycle possèdent chacun leur propre type de
résultat afin d’éviter toute dépendance contractuelle cross-owner.

## Atomicité

Une mutation, son état courant et sa révision éventuelle sont écrits dans une
transaction locale. Lorsqu’une transaction englobante existe, le store y
participe sans la valider ni l’annuler. Aucune transaction ACID multi-owner
n’est créée.

## Scénarios certifiés

Les tests PostgreSQL couvrent replay, divergence, tentative de takeover,
optimistic locking, révisions append-only, délégations, checkpoint monotone et
deux mises à jour concurrentes d’un même brouillon. La concurrence converge vers
une seule application et un conflit de version explicite.
