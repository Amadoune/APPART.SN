# Writer Evidence

La matérialisation réutilise exclusivement `SearchDecisionReader` et `SearchDecisionWriter` existants. Aucune écriture SQL n’est présente dans la couche Application.

Les résultats writer sont conservés sans fallback : `Applied`, `AlreadyApplied`, `RejectedObsolete`, `Divergent`.

Le test PostgreSQL utilise le store productif et confirme une décision persistée lisible par le reader existant.
