# Place Registry Audit

| Opération | Sémantique fermée actuelle |
|---|---|
| `find(PlaceId)` | Aggregate détaché ou `null`; aucun événement Domain persistant reconstitué |
| `add(Place)` | ajout atomique ; `DuplicatePlaceId` sur id existant ; `DuplicatePlaceCode` sur `(countryCode, code)` existant |
| `save(Place, expectedVersion)` | snapshot propre sauvegardé uniquement si version persistée = expectedVersion ; sinon `ConcurrentPlaceModification` |

Les erreurs de persistence non métier devront être réduites en exception d'intégrité Geography dédiée suivant les conventions repository, sans modifier le contrat durant F0.

`Place` démarre à version 1 et incrémente lors de rename, merge, disable et enable. `save` exige une version candidate strictement supérieure à expectedVersion.
