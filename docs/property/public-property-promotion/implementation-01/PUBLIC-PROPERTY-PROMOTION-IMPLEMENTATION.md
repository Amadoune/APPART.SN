# F6 — Public Property Promotion Implementation 01

## Objet

F6 matérialise la frontière applicative autorisée entre un `PropertyAuthoringState` complet et l’Aggregate `Property` de RealEstateCatalog. L’implémentation est owner-scoped, déterministe et fail-closed. Elle ne lit ni Search ni Projection.

## Chaîne exécutée

`PropertyAuthoringStore` → contrôle owner/version/complétude → Value Objects Domain → `AddressIdentityIssuerV1` → `BusinessYearAuthorityV1` → `Address` → `RegisterProperty` → `GeographicPlaceCatalog` productif → `PropertyRegistry`.

`RegisterProperty`, `PropertyTypePolicy`, `GeographicPlaceCatalog` et les autorités F0–F5-A n’ont pas été modifiés.

## Résultat

La promotion crée un Aggregate réel dans `real_estate_catalog.properties`. Le Submit Listing appelle cette frontière avant toute transition vers `Submitted` et ne continue que pour `Applied` ou `AlreadyApplied`.
