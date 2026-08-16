# Matrice source vers Aggregate

| Source | Autorité | Destination | Qualification |
|---|---|---|---|
| PropertyId Authoring | F4/F6 | Property.id | certifiée |
| référence/type/faits physiques | F4 + Value Objects | Property | certifiée |
| PropertyId + AddressIntentId | F2 UUIDv5 | Address.id | création initiale certifiée |
| GeographicPlaceId | F5-A/F0 | Address.placeId | création initiale certifiée |
| AddressLine | F4 | Address.line | certifiée |
| occurredAt | F3 UTC | BusinessYear de validation | certifiée |
| Aggregate existant AddressId | comparaison F6 | décision compatible/divergente | **NON_CERTIFIED : AddressId omis** |

La matrice ne peut satisfaire l’interdiction `NON_CERTIFIED` ; verdict NO GO obligatoire.
