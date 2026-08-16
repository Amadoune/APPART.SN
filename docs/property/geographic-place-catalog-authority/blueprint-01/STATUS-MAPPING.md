# Status Mapping

## Mapping fermé qualifié

| Observation autoritative | Statut |
|---|---|
| `find(...) === null` | `NotFound` |
| `mergedInto() !== null` | `Merged` |
| `mergedInto() === null && !isEnabled()` | `Disabled` |
| non fusionnée, enabled, type déclaré adressable par une autorité | `Usable` |
| non fusionnée, enabled, type déclaré non adressable par une autorité | `NotAddressable` |

## Priorité

`Merged` prime sur `Disabled`. Cette priorité conserve la cause lifecycle la plus précise et reste stable avec l’invariant Geography selon lequel une Place fusionnée est désactivée.

## Cas hors statut

Une reconstruction corrompue ou une dépendance indisponible ne constitue pas un état de Place. Ces cas ne sont mappés vers aucun `GeographicPlaceStatus` et remontent en erreur.

## Écart historique

Dans le Blueprint initial, les trois premières lignes étaient directement exécutables tandis que les deux dernières ne possédaient aucune autorité. Cet écart a produit le NO GO historique et ne doit pas être effacé.

## Completion F5-A1

F5-A1 ferme désormais les deux dernières lignes avec une policy RealEstateCatalog Domain :

- Country, Region, Department → `NotAddressable` ;
- City, District, Neighborhood → `Addressable`, donc `Usable` lorsque les contrôles précédents ont réussi.

Le mapping est exhaustif, sans `default` permissif. L’ordre existence → merge → enabled → addressability est obligatoire. La policy n’est jamais appelée pour une absence, une Place merged ou une Place disabled.
