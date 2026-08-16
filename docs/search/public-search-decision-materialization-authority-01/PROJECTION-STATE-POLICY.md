# Projection State Policy

La règle existe dans `SearchVisibilityPolicy` :

| Sources | Résultat |
|---|---|
| Listing terminal ou Property archivée | `Removed` |
| Listing non Published, Property non Available ou Media non Ready | `Hidden` |
| Listing Published + Property Available + Media Ready | `Visible` |

Cette règle est owner SearchDiscovery, déterministe et indépendante du Projection Store. Elle est réutilisable par un futur matérialiseur.

Le gap restant n'est pas la policy mais la construction productive des états `ListingSearchState`, `PropertySearchState` et `MediaSearchState` depuis les owners correspondants.

## Completion 01

Ce gap devient une frontière d'adaptation mécanique autorisée à Implementation 01 : les adapters owners produisent les trois enums et leurs révisions ; `SearchVisibilityPolicy` demeure l'unique décideur de `Visible`, `Hidden` ou `Removed`.
