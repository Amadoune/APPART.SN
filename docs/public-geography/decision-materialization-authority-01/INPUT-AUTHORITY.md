# Input authority

| Input | Owner/contract | Révision disponible |
|---|---|---|
| PlaceId, type, parent, nom, enabled/merged | Geography `PlaceRegistry`/repository owner | `aggregate_version` du Place |
| chaîne des parents | Geography, lecture parentale répétée | version individuelle de chaque Place |
| relation Listing → Property → Address → Place | Listing/Property contracts | versions owner respectives |
| URL publique de chaque breadcrumb | aucune authority trouvée | aucune |
| séquence unique couvrant la représentation complète | aucune authority trouvée | aucune |

Les deux derniers éléments constituent une seule lacune : la représentation publique révisionnée d'un Place.
