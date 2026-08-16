# Policy Contract

## Contrat documentaire futur

Nom : `GeographicPlaceAddressabilityPolicy`.

Entrée minimale : un `PlaceType` Geography valide et déjà reconstitué.

Sortie fermée recommandée :

- `Addressable` ;
- `NotAddressable`.

Une décision explicite est préférable à un booléen afin de conserver un catalogue fermé, lisible et exhaustif.

## Propriétés

- pure et déterministe ;
- sans lookup ;
- sans persistence ;
- sans clock ;
- sans configuration runtime ;
- indépendante de `PropertyType` ;
- exhaustive sur les six cases actuels de `PlaceType`.

Un type inconnu ou corrompu ne peut pas atteindre la policy : la reconstruction Geography doit échouer auparavant. Aucun fallback `NotAddressable` ou `Addressable` n’est autorisé pour une valeur inconnue.
