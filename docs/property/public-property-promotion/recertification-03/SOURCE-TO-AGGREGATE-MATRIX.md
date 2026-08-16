# Matrice terminale source vers Aggregate

| Source autoritative | Validation | Effet Aggregate |
|---|---|---|
| Property Authoring complète et owner/version valides | F5 completeness | admissible |
| PropertyId + AddressIntentId | F2 Address Identity | AddressId canonique |
| occurredAt de commande | F3 UTC Business Year | BusinessYear exact |
| GeographicPlaceId | Catalog F5-A | Usable requis |
| faits Property | RegisterProperty + PropertyTypePolicy | Aggregate initial version 0 |
| commande Promotion | ledger 100 | Applied/AlreadyApplied/Divergent fermé |
| résultat Promotion Applied ou AlreadyApplied | Submit composition | transition Listing autorisée |
| autre résultat Promotion | réduction fermée | Listing non Submitted |

Toutes les lignes sont certifiées; aucune ligne UNKNOWN, MISSING ou NON_CERTIFIED.
