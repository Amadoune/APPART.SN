# Structured Address Authority

`Address` contient exactement :

- `AddressId` non nullable ;
- `GeographicPlaceId` non nullable ;
- `AddressLine` non nullable.

Elle est requise par `PropertyTypePolicy` pour Apartment, House, Villa, Land, Office et Commercial. Elle peut être `null` uniquement pour `Other`.

Sources cibles :

- `AddressLine` : saisie owner-scoped dans Authoring, validée par `AddressLine` ;
- `GeographicPlaceId` : sélection depuis Geography, puis validation `Usable` par RealEstateCatalog ;
- `AddressId` : identité technique émise par une autorité RealEstateCatalog/Authoring dédiée.

Une adresse partielle n'est pas constructible avec le modèle actuel. City/neighborhood seuls ne satisfont aucun de ces trois invariants. L'autorité d'émission d'`AddressId` n'est pas observée pour le handoff projeté et reste un blocage explicite.
