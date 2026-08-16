# Cas de compatibilité Address

| Cas | Décision |
|---|---|
| mêmes AddressId, PlaceId et AddressLine | compatible |
| AddressId différent, mêmes PlaceId et AddressLine | `DivergentCommand` |
| AddressId identique, PlaceId différent | `DivergentCommand` |
| AddressId identique, AddressLine différente | `DivergentCommand` |
| Address attendue null, existante non null | `DivergentCommand` |
| Address attendue non null, existante null | `DivergentCommand` |
| deux Address null | compatible si tous les autres champs convergent |

Aucune redirection, réécriture d’identité ou normalisation de rattrapage n’est autorisée.
