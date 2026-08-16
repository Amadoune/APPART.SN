# Physical Property Facts Authority

Owner des faits : propriétaire via Property Authoring. Autorité de validation : RealEstateCatalog Domain.

| Champ cible | Type documentaire | Nullable | Validation existante |
|---|---|---:|---|
| `surfaceSquareMeters` | entier conforme à `SurfaceArea` | Oui seulement pour Other | `SurfaceArea` + `PropertyTypePolicy` |
| `rooms` | entier conforme à `RoomCount` | Non | Land=0 ; minimum 1 pour résidentiel/Office/Commercial ; règles Other |
| `bathrooms` | entier conforme à `BathroomCount` | Non | Land=0 ; cohérence avec rooms lorsque la policy l'impose |
| `constructionYear` | entier conforme à `ConstructionYear` | Oui | null pour Land ; non futur par rapport à BusinessYear |

Chaque valeur appartient au même snapshot/version Authoring. Aucun zéro, millésime ou surface n'est injecté comme défaut. Le caractère obligatoire est évalué mécaniquement à partir de `propertyType` et des règles Domain existantes.
