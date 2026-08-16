# Preuves Geography

| État Catalog F5-A | Réduction Promotion | Effet |
|---|---|---|
| City, District ou Neighborhood enabled et non merged | `Usable` puis `Applied` | Property admissible |
| UUID absent | `NotFound` puis `DomainRejected` | aucune Property, aucun ledger, aucun Submit |
| Place disabled | `Disabled` puis `DomainRejected` | aucune Property, aucun Submit |
| Place merged | `Merged` puis `DomainRejected` | aucune redirection, aucune Property, aucun Submit |
| Country, Region ou Department enabled | `NotAddressable` puis `DomainRejected` | aucune Property, aucun Submit |
| dépendance indisponible ou donnée corrompue | `DependencyUnavailable` | fail-closed, aucune mutation partielle |

Les campagnes unitaires et PostgreSQL couvrent les statuts positifs et négatifs. Aucun fallback vers `NotFound` ou `Usable` n’est présent.
