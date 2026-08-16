# Catalog Integration

## Mapping exhaustif futur

1. `PlaceRegistry::find(...) === null` → `NotFound`.
2. `mergedInto() !== null` → `Merged`.
3. `mergedInto() === null && !isEnabled()` → `Disabled`.
4. Place enabled et non merged + policy `NotAddressable` → `NotAddressable`.
5. Place enabled et non merged + policy `Addressable` → `Usable`.

L’ordre est normatif. La policy n’est pas appelée pour une Place absente, merged ou disabled et ne peut donc masquer un état lifecycle plus précis.

## Erreurs

Une corruption de type/snapshot ou une indisponibilité Geography reste hors du canal `GeographicPlaceStatus` et interrompt l’opération fail-closed. Ces erreurs ne deviennent jamais `NotFound`, `NotAddressable` ou `Usable`.

## Lecture seule

L’intégration ne suit pas une merge target, ne modifie pas Place et ne consulte ni F1, ni Projection, ni Search.
