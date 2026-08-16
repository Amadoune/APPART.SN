# ChangeAddress Compatibility

Le Domain actuel reçoit une nouvelle instance `Address` dans `ChangeAddress` et remplace l'ancienne. `Address::equals` ignore AddressId et compare les faits physiques PlaceId + AddressLine.

Décision compatible :

- même faits physiques : aucune nouvelle intention ; le Domain refuse déjà le changement inchangé ; l'ID existant demeure ;
- PlaceId ou AddressLine modifié : nouvelle intention serveur, nouvel addressIntentId et nouvel AddressId ;
- retry du même changement : même addressIntentId et même AddressId.

Cette règle n'altère pas `ChangeAddress`; elle prépare une future orchestration d'identité cohérente avec son comportement existant.
