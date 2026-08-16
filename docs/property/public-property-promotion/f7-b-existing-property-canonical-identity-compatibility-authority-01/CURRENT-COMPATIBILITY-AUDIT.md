# Audit de la compatibilité actuelle

`compatible()` vérifie actuellement :

| Élément | Comparaison actuelle | Autorité |
|---|---|---|
| PropertyId | implicite par lookup | commande/snapshot |
| version Aggregate | stricte `0` | Aggregate |
| PropertyReference | égalité Domain | snapshot |
| PropertyType | enum stricte | snapshot/policy |
| SurfaceArea | valeur nullable | snapshot |
| RoomCount | égalité Domain | snapshot |
| BathroomCount | égalité Domain | snapshot |
| ConstructionYear | valeur nullable | snapshot/F3 policy |
| Address | `Address::equals()` | F2 + F4 + F5-A |
| AddressId | **non comparé** | F2 |
| GeographicPlaceId | égalité Domain via Address | F5-A |
| AddressLine | égalité Domain via Address | F4 |

La seule lacune qualifiée est AddressId. Property status n’exige pas une règle supplémentaire : l’état initial canonique est version 0 ; toute transition mutable existante incrémente la version et devient divergente.
