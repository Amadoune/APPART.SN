# Result Catalog

Catalogue fermé de l'Issuer V1 :

| Statut | Sémantique |
|---|---|
| `Issued` | AddressId déterministe calculé et validé par le Value Object |
| `Collision` | L'intégration démontre que l'identité correspond à une intention/faits incompatibles |

`AlreadyIssued` n'est pas nécessaire : la fonction pure réémet la même valeur sans mutation. `VersionConflict` et `DivergentIntent` appartiennent à Authoring/Promotion avant l'appel. `DependencyUnavailable` est impossible pour l'Issuer pur ; les indisponibilités Registry restent des résultats de promotion.
