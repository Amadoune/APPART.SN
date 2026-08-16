# Payload model

Champs persistés :

| Champ | Source/validation |
|---|---|
| locality | doit provenir d'une règle Geography publique; non vide |
| breadcrumb labels | hiérarchie/noms owner; ordre public à certifier |
| breadcrumb URLs | aucune source/politique productive; URL valide obligatoire |

Aucun type, parentId, ListingId ou label legacy ne doit être ajouté par commodité. Le payload ne peut être finalisé tant que la règle canonique des URL et de l'ordre public manque.

## Completion 01

La règle est désormais fermée en V2 : aucune URL; locality terminale; breadcrumb root→leaf d'items `placeId`, `type`, `officialName`, `parentPlaceId`, `aggregateVersion`; revisionVector ordonné. Le texte historique ci-dessus reste la preuve du premier NO GO.
