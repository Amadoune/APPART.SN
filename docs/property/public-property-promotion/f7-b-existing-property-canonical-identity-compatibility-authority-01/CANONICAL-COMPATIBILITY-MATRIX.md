# Matrice complète de compatibilité canonique

| Champ attendu / existant | Même | Différent | Nullabilité | Comparaison normative |
|---|---|---|---|---|
| PropertyId | compatible | divergent | non | identité stricte/lookup |
| Aggregate version initiale | compatible si 0 | divergent | non | entier strict |
| PropertyReference | compatible | divergent | non | égalité Domain |
| PropertyType | compatible | divergent | non | enum stricte |
| SurfaceArea | compatible | divergent | oui | deux null ou même valeur |
| RoomCount | compatible | divergent | non | égalité Domain |
| BathroomCount | compatible | divergent | non | égalité Domain |
| ConstructionYear | compatible | divergent | oui | deux null ou même valeur |
| Address présence | compatible si même présence | divergent | oui | comparaison explicite |
| AddressId | compatible | divergent | dans Address non null | égalité d’identité F2 |
| GeographicPlaceId | compatible | divergent | dans Address non null | égalité Domain |
| AddressLine | compatible | divergent | dans Address non null | égalité Domain |

Tous les champs doivent converger simultanément. Une seule différence suffit pour `DivergentCommand`.
