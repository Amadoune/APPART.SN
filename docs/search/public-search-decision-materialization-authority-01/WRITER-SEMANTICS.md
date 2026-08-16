# Writer Semantics

`PostgreSqlSearchDecisionWriter` verrouille la ligne par ListingId puis applique :

| Situation | Résultat |
|---|---|
| aucune décision existante | `Applied` |
| version entrante supérieure | `Applied` |
| même version, checksum canonique identique | `AlreadyApplied` |
| même version, checksum différent | `Divergent` |
| version entrante inférieure | `RejectedObsolete` |

Le checksum couvre état, rank, facettes ordonnées et révisions. Le writer rejoint une transaction existante ou gère une transaction locale avec savepoint.

Il persiste une décision déjà valide; il ne décide ni identité, ni version, ni rang, ni facets. Aucune modification du writer n'est requise ou autorisée par cette Authority.
