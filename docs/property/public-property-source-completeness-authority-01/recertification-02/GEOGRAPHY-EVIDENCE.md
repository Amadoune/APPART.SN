# Geography Evidence

## Chaîne productive

`GeographicPlaceId` → `GeographicPlaceCatalog` → `GeographyBackedGeographicPlaceCatalog` → `PlaceRegistry::find` → `PostgreSqlPlaceRepository` → `geography.places`.

Le binding container résout réellement l’adapter et le repository PostgreSQL comme singletons. Aucun `FakeGeographicPlaceCatalog` n’est utilisé dans la preuve PostgreSQL.

## Résultats exécutables

- Place City/District/Neighborhood enabled non merged → `Usable` ;
- UUID absent → `NotFound` ;
- Place disabled → `Disabled` ;
- Place merged → `Merged` avec priorité sur disabled ;
- Country/Region/Department enabled → `NotAddressable`.

La preuve d’assemblage crée une vraie hiérarchie F0 Country → Region → City, persiste les Aggregates par `PostgreSqlPlaceRepository`, puis obtient `Usable` pour la City.

Corruption et indisponibilité remontent sans statut de secours. Aucun label, city ou neighborhood ne fabrique l’identité.
