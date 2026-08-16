# F5-A Blueprint Completion 01

## Objet

Cette completion réévalue exclusivement le blocker du Blueprint F5-A initial. Elle ne remplace pas son historique et ne crée aucune implémentation.

## Blocker fermé

Ancien état : Place existante, enabled et non merged → choix `Usable`/`NotAddressable` inconnu.

Autorité intégrée : F5-A1 attribue la règle à RealEstateCatalog Domain et classe exhaustivement les six `PlaceType`.

Nouvel état : la policy produit `NotAddressable` pour Country, Region et Department, et `Addressable` pour City, District et Neighborhood. Le Catalog peut donc produire un statut unique après ses contrôles lifecycle.

## Blueprint complété

- source : `PlaceRegistry::find` ;
- adapter : Infrastructure RealEstateCatalog ;
- policy : RealEstateCatalog Domain ;
- priorité : existence, merge, enabled, addressability ;
- erreurs techniques : exception fail-closed, aucun statut métier ;
- mutation : aucune ;
- consumers : RegisterProperty et ChangeAddress, même décision ;
- F1/F4 : acquisition et preuve de sélection uniquement ;
- migration : aucune attendue.

## Verdict

La divergence historique est levée sans en substituer une autre. **GO PROPOSÉ.**
