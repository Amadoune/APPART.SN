# Contract Audit

## Port existant

`GeographicPlaceCatalog::statusOf(GeographicPlaceId): GeographicPlaceStatus` appartient à RealEstateCatalog. Il ne déclare aucune exception et son commentaire définit `Usable` comme existante, enabled, non fusionnée et adressable.

## Catalogue fermé existant

`GeographicPlaceStatus` contient exactement :

- `Usable` ;
- `NotFound` ;
- `Disabled` ;
- `Merged` ;
- `NotAddressable`.

Le contrat représente donc les états métier attendus, mais ne définit pas la policy qui sépare `Usable` de `NotAddressable`.

## Consumers

- `RegisterProperty` consulte le port avant de construire et persister Property lorsqu’une adresse est fournie.
- `ChangeAddress` applique le même contrôle avant la mutation d’adresse.

Tous deux n’acceptent que `Usable` et lèvent `UnavailableGeographicPlace` pour tout autre statut.

## Implémentations et tests

Aucune implémentation productive ni aucun binding n’est inventorié. `FakeGeographicPlaceCatalog` est le seul implémentant observé. `PropertyUseCasesTest` couvre notamment `Merged` et `Disabled`, mais pas une composition Geography productive, `NotAddressable`, la corruption ou l’indisponibilité.

## Exceptions

Le port n’a pas de canal typé pour corruption ou indisponibilité. Cela n’autorise pas leur réduction en statut métier. Les erreurs de la source doivent remonter et provoquer un arrêt fermé ; une réduction applicative typée éventuelle exige un chantier séparé si le consumer HTTP doit les distinguer.
