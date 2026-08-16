# Failure Modes

| Cas | Décideur | Résultat / comportement |
|---|---|---|
| Parent absent | Selection Reader | `Missing`, aucun item |
| Place candidate absente | Selection Reader/source | Non retournée ; lookup parent absent = `Missing` |
| Aucune Place sélectionnable | Selection Reader | `Empty` |
| Donnée corrompue | Selection Reader | `Corrupted`, aucune liste partielle |
| Dépendance indisponible | Selection Reader | `DependencyUnavailable` |
| Place disabled après sélection | RealEstateCatalog revalidation | Statut non Usable ; promotion refusée |
| Place merged après sélection | RealEstateCatalog revalidation | Ancien ID refusé ; aucune redirection implicite |
| Place dite archived | Non applicable | Geography ne possède pas cet état ; ne pas l'inventer |
| Mutation concurrente pendant pagination | Selection Reader | Keyset sur état observé ; relecture autorisée, aucune garantie snapshot globale |

Le reader ne compense ni ne modifie Geography.
