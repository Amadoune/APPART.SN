# Authoring Completeness Evidence

`PropertyAuthoringState` expose réellement : propertyId, ownerAccountId, propertyReference, propertyType, surfaceSquareMeters, rooms, bathrooms, constructionYear nullable, geographicPlaceId, addressLine et addressIntentId.

`PropertyAuthoringSourceCompleteness::CompleteForPromotion` exige les sources non optionnelles de promotion. ConstructionYear demeure nullable conformément à RegisterProperty.

Preuves exécutées :

- normalisation et complétude d’un état enrichi F4 ;
- replay geography invalid/unavailable fail-closed ;
- stabilité/rotation de l’AddressIntent ;
- migration additive 099 et persistance PostgreSQL ;
- snapshot legacy reconstruit avec champs nouveaux null et `IncompleteForPromotion`.

City et neighborhood legacy ne créent ni PlaceId ni AddressIntent. Aucun backfill ou fallback n’existe.
