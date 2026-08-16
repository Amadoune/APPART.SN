# Result Catalog

Le catalogue minimal compatible avec les preuves serait :

- `Applied` ;
- `AlreadyApplied` ;
- `SourceMissing` ;
- `SourceCorrupted` ;
- `Obsolete` ;
- `Divergent` ;
- `DependencyUnavailable`.

Correspondances writer : `RejectedObsolete → Obsolete`, `Divergent → Divergent`.

Ce catalogue n'est pas certifié comme nouveau contrat, car la construction de décision s'arrête avant le writer sur l'absence de rank. Il constitue uniquement l'inventaire fermé des résultats justifiés, non une autorisation d'implémenter.

## Completion 01

Le catalogue final est maintenant certifié par `FINAL-RESULT-CATALOG.md` avec les noms exacts `Applied`, `AlreadyApplied`, `SourceMissing`, `SourceCorrupted`, `RejectedObsolete`, `Divergent` et `DependencyUnavailable`.
