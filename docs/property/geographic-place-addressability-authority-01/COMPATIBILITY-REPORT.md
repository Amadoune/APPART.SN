# Compatibility Report

## Préservation des frontières

- Geography Domain et `PlaceType` restent inchangés et propriétaires de la taxonomie/hierarchie.
- F1 conserve sa lecture générale de navigation et sélection.
- F4-A/F4 conservent leur replay owner-scoped sans devenir autorités d’adressabilité.
- RealEstateCatalog reste owner de `Address` et de son acceptabilité géographique.
- `GeographicPlaceStatus`, `GeographicPlaceCatalog`, `RegisterProperty`, `ChangeAddress` et leurs exceptions restent inchangés.
- Projection et Search restent aval et absents de la décision.

## Failure modes

| Situation | Traitement |
|---|---|
| type addressable valide | décision `Addressable`, puis `Usable` si lifecycle valide |
| type non addressable valide | décision `NotAddressable` |
| type inconnu/corrompu | échec de reconstruction, policy non appelée |
| Place merged | `Merged`, policy non appelée |
| Place disabled | `Disabled`, policy non appelée |
| Place missing | `NotFound`, policy non appelée |

## Changement produit

Aucun. Cette Authority fixe une décision documentaire préalable à une implémentation séparée.
