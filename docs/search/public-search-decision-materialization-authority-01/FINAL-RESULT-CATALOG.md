# Final Result Catalog

Catalogue fermé du matérialiseur :

- `Applied` ;
- `AlreadyApplied` ;
- `SourceMissing` ;
- `SourceCorrupted` ;
- `RejectedObsolete` ;
- `Divergent` ;
- `DependencyUnavailable`.

Les quatre résultats Writer sont réduits sans ambiguïté vers `Applied`, `AlreadyApplied`, `RejectedObsolete` et `Divergent`. Toute exception de dépendance est fail-closed ; aucune décision partielle n'est exposée.
