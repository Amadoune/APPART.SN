# Addressability Integration

## Policy consommée

`GeographicPlaceAddressabilityPolicy` appartient à RealEstateCatalog Domain. Elle reçoit un `PlaceType` valide et retourne un résultat fermé `Addressable` ou `NotAddressable`.

| PlaceType | Décision |
|---|---|
| Country | NotAddressable |
| Region | NotAddressable |
| Department | NotAddressable |
| City | Addressable |
| District | Addressable |
| Neighborhood | Addressable |

La policy est pure, déterministe, indépendante de `PropertyType`, sans lookup, clock, persistence ou configuration runtime.

## Composition cible

`GeographicPlaceCatalog` → adapter RealEstateCatalog Infrastructure → `PlaceRegistry::find` + `GeographicPlaceAddressabilityPolicy`.

L’adapter observe d’abord existence et lifecycle. Il consulte la policy uniquement pour une Place valide, enabled et non merged. Il transforme `Addressable` en `Usable` et `NotAddressable` en statut homonyme.

La matrice n’est pas recopiée dans l’adapter, F1 ou le workspace. Toute évolution reste gouvernée par une nouvelle décision RealEstateCatalog.
