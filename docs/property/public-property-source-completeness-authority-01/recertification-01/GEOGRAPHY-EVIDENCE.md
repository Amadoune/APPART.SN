# Geography Evidence

## Chaîne démontrée

F1 lit les Places PostgreSQL enabled et non fusionnées. F4-A expose cette lecture au workspace, conserve ID/type/parent/cursor/limit et rejoue exactement la sélection. F4 n’écrit le `geographicPlaceId` qu’après `Validated`. City, neighborhood et les labels ne fabriquent aucune identité.

## Première divergence

`RegisterProperty` ne consomme pas le reader F1 : il exige `GeographicPlaceCatalog::statusOf(GeographicPlaceId)`. Le dépôt contient le port et des fakes de tests, mais aucune classe productive `implements GeographicPlaceCatalog`, aucun Provider et aucun binding.

`PostgreSqlPlaceRepository::find` constitue une autorité Geography interne potentiellement composable. La traduction vers `Usable`, `Disabled`, `Merged` ou `Missing` reste néanmoins une capacité non matérialisée. F5 n’est pas autorisée à créer cet adaptateur.
