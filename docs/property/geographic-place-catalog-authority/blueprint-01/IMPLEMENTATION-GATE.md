# Implementation Gate

## Composants autorisés pour F5-A Implementation 01

- contrat Domain `GeographicPlaceAddressabilityPolicy` ;
- résultat/enum fermé `Addressable` / `NotAddressable` ;
- implémentation déterministe des six cases ;
- adapter Infrastructure implémentant `GeographicPlaceCatalog` ;
- Provider et binding productifs ;
- tests ciblés.

Aucune migration, modification Geography, F1/F4, RegisterProperty ou ChangeAddress n’est attendue.

## Matrice de tests obligatoire

Policy : les six `PlaceType` avec leur verdict normatif.

Catalog : missing, merged, disabled, enabled City/District/Neighborhood → `Usable`, enabled Country/Region/Department → `NotAddressable`, corruption et indisponibilité fail-closed, priorité merge/disabled.

Composition : binding productif, RegisterProperty sans fake et ChangeAddress sans fake.

PostgreSQL : vraie Place F0 usable, disabled, merged et missing.

Architecture : adapter read-only, aucun SQL Application, aucune Projection/Search, aucune dépendance inverse Geography → RealEstateCatalog.

## Handoff

Après GO de l’Implementation, F5 devra démontrer : snapshot F4 → AddressId F2 → BusinessYear F3 → Catalog productif → entrées RegisterProperty complètes, sans fake ni fallback.

Le présent document qualifie ce gate mais ne l’ouvre ni ne l’implémente.
