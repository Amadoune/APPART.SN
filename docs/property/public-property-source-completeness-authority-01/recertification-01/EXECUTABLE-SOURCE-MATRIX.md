# Executable Source Matrix

| Source | Owner | Représentation | Exécution actuelle |
|---|---|---|---|
| propertyId / owner | Property Authoring + session IAM | UUID / AccountId | Oui |
| propertyReference | propriétaire via F4 | `PropertyReference` | Oui |
| propertyType | propriétaire via Authoring | `PropertyType` | Oui |
| surface / rooms / bathrooms / year | propriétaire via F4 | Value Objects physiques | Oui |
| geographicPlaceId | Geography sélectionnée via F1/F4-A | PlaceId persisté | Oui |
| statut Geography à la Promotion | Geography → RealEstateCatalog | `GeographicPlaceCatalog::statusOf` | **Non : aucun adaptateur/binding productif** |
| addressLine | propriétaire via F4 | `AddressLine` canonique | Oui |
| addressIntentId → AddressId | Property Authoring + F2 | UUID serveur → UUIDv5 | Oui |
| occurredAt → BusinessYear | commande stable + F3 | instant absolu → `BusinessYear` | Autorité exécutable |

La matrice comporte une source nécessaire non exécutable ; un GO est interdit.
