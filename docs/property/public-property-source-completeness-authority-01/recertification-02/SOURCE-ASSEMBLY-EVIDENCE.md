# Source Assembly Evidence

`PostgreSqlSourceAssemblyCertificationTest` construit un `PropertyAuthoringState` complet, puis assemble sans Promotion :

- PropertyId ;
- PropertyReference ;
- PropertyType ;
- SurfaceArea ;
- RoomCount ;
- BathroomCount ;
- ConstructionYear ;
- GeographicPlaceId ;
- AddressLine ;
- AddressIntentId → AddressId F2 ;
- occurredAt stable → BusinessYear F3 ;
- Address ;
- statut Geography `Usable` via repository PostgreSQL F0 et adapter F5-A.

Le test s’arrête avant `RegisterProperty::execute` et n’appelle aucun `PropertyRegistry::add`. Résultat : PASS, 1 test, 12 assertions.

Cette preuve n’emploie ni fake Catalog, ni clock courante, ni valeur AddressId/BusinessYear client, ni Projection/Search, ni SQL Application direct.
