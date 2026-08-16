# Geographic Place Catalog Implementation 01

## Résultat

`GeographicPlaceCatalog::statusOf` possède désormais une composition productive Geography-backed :

`GeographicPlaceCatalog` → `GeographyBackedGeographicPlaceCatalog` → `PlaceRegistry::find` → `PostgreSqlPlaceRepository` → `geography.places`.

L’adapter injecte également `GeographicPlaceAddressabilityPolicy`, policy pure RealEstateCatalog Domain dont la sortie fermée est `GeographicPlaceAddressability::Addressable|NotAddressable`.

## Matrice implémentée

- Country, Region, Department → NotAddressable ;
- City, District, Neighborhood → Addressable ;
- absence → `NotFound` ;
- merged → `Merged` ;
- disabled non merged → `Disabled` ;
- enabled non merged + NotAddressable → `NotAddressable` ;
- enabled non merged + Addressable → `Usable`.

Le `match` de policy n’a aucun `default`. L’identité est convertie par `PlaceId::fromString($geographicPlaceId->value)` sans génération ni lookup par label.

## Erreurs et lecture seule

L’adapter ne capture aucune erreur du registry : corruption, reconstruction impossible et indisponibilité remontent fail-closed. Il n’appelle que `find`, ne suit jamais `mergedInto` et n’appelle ni `add` ni `save`.

## Composition

`GeographicPlaceCatalogServiceProvider` enregistre la policy et l’adapter comme singletons, puis lie nominativement le port à l’adapter. Le Provider F0 continue de lier `PlaceRegistry` au repository PostgreSQL.

`RegisterProperty` et `ChangeAddress` n’ont pas été modifiés. Leurs tests de composition PostgreSQL utilisent le catalogue productif, sans `FakeGeographicPlaceCatalog`.
