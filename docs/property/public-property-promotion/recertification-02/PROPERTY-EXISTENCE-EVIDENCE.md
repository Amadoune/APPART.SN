# Preuve Property déjà existante

## Comparaison actuelle

F6 compare référence, type, surface, pièces, salles de bain, année de construction et adresse. Pour l’adresse, `Address::equals()` ne compare que :

- GeographicPlaceId ;
- AddressLine.

`AddressId` est omis.

## Conséquence

Une Property existante avec la même adresse descriptive mais un AddressId différent de celui produit par `PropertyId + AddressIntentId → F2 UUIDv5` est classée compatible.

Résultat actuel possible : `AlreadyApplied`. Résultat requis : `DivergentCommand` pour identité canonique incompatible.

La preuve compatible/incompatible est donc **NON CERTIFIABLE**. Cause racine unique du NO GO.
