# Geographic Place Catalog Authority — Blueprint 01

## Décision

Le port `RealEstateCatalog\Application\Contract\GeographicPlaceCatalog` doit être servi par un adaptateur read-only situé dans l’Infrastructure RealEstateCatalog et alimenté par l’autorité `Geography\Application\Contract\PlaceRegistry` liée à `PostgreSqlPlaceRepository`.

La source permet de distinguer sans ambiguïté l’absence, l’activation et la fusion. Le mapping correspondant est fermé : `null` devient `NotFound`, une fusion devient `Merged` avant toute lecture de l’activation, une Place non fusionnée désactivée devient `Disabled`.

Le blueprint ne peut toutefois pas qualifier `Usable` contre `NotAddressable`. Le contrat définit ces deux résultats et précise qu’une Place usable est « addressable », mais ni `Place`, ni `PlaceType`, ni une policy inventoriée ne porte la règle d’adressabilité. Déduire cette règle dans l’adaptateur créerait une règle métier nouvelle.

## Frontière cible

`RegisterProperty` → `GeographicPlaceCatalog` → adaptateur Infrastructure RealEstateCatalog → `PlaceRegistry::find` → `PostgreSqlPlaceRepository` → `geography.places`.

L’adaptateur ne suit pas une cible de fusion, ne transforme pas un label en identité et ne mute aucun état.

## Échec fermé

`PersistentPlaceIntegrity` et toute indisponibilité de lecture remontent hors du canal des statuts. Elles ne deviennent jamais `NotFound`, `Disabled` ou `Usable`. `RegisterProperty` n’atteint alors ni `Property::register` ni `PropertyRegistry::add`.

## Gate

La source, l’ownership, le binding cible, la priorité merge/disabled, TOCTOU et les erreurs sont qualifiés. La règle permettant de produire `Usable` ou `NotAddressable` reste absente. L’implémentation F5-A ne doit donc pas être ouverte avant qualification de cette autorité minimale.

**Verdict : NO GO PROPOSÉ — première et unique cause restante : autorité d’adressabilité Geography absente.**

---

## Completion 01 — clôture du blocker

Le verdict ci-dessus constitue l’historique certifié du Blueprint initial. F5-A1 Geographic Place Addressability Authority 01 a depuis attribué la policy à RealEstateCatalog Domain et fixé la matrice normative suivante : Country, Region et Department sont `NotAddressable`; City, District et Neighborhood sont `Addressable`.

Le choix auparavant inconnu entre `Usable` et `NotAddressable` est donc désormais fermé. L’adapter cible consomme la policy ; il ne possède ni ne redéfinit sa matrice.

Le mapping complet devient : absence → `NotFound`; merge → `Merged`; disabled non merged → `Disabled`; enabled non merged non addressable → `NotAddressable`; enabled non merged addressable → `Usable`. Corruption et indisponibilité restent hors du catalogue métier et échouent fermées.

La Completion 01 remplace le gate courant, sans réécrire le résultat historique : **GO PROPOSÉ — Blueprint complet, Implementation F5-A désormais qualifiable séparément.**
