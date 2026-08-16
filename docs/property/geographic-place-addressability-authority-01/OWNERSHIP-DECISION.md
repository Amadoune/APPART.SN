# Ownership Decision

## Options auditées

Geography Domain possède `PlaceType`, la hiérarchie, `enabled` et `mergedInto`. Il peut affirmer ce qu’est une City ou un Department, mais pas si ce niveau suffit pour une Address propre à un autre bounded context.

RealEstateCatalog possède `Address`, `GeographicPlaceCatalog`, `GeographicPlaceStatus::NotAddressable`, `RegisterProperty` et `ChangeAddress`. La décision à prendre est explicitement « addressable for Property ».

## Owner retenu

**RealEstateCatalog Domain** est l’owner unique de `PlaceType → addressable for Property`.

L’Infrastructure RealEstateCatalog ne fait qu’adapter le `PlaceType` observé dans Geography vers cette policy. Geography n’importe aucun contrat RealEstateCatalog et ne connaît aucune règle Property.

## Conséquence

Une évolution du catalogue Geography ne change pas silencieusement l’adressabilité Property. Tout nouveau type ou changement de verdict exige une décision RealEstateCatalog explicite.
