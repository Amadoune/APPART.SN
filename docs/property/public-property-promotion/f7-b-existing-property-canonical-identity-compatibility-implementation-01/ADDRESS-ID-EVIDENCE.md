# F7-B — Preuve AddressId

## Autorité comparée

Lorsque les deux adresses existent, la promotion exige cumulativement :

1. `existing->id->equals(expected->id)` ;
2. `existing->equals(expected)`.

La première condition protège l’identité canonique F2. La seconde conserve l’égalité descriptive historique sur GeographicPlaceId et AddressLine.

## Preuves exécutées

Les tests unitaires couvrent les quatre combinaisons de présence, l’AddressId divergent seul, le PlaceId divergent seul, l’AddressLine divergente seule et les deux adresses nulles.

Le test PostgreSQL persiste une Property avec le même PlaceId et la même AddressLine mais un AddressId non canonique. Le résultat est `DivergentCommand`, l’unique Aggregate reste inchangé et aucun ledger de succès n’est créé.

## Frontières inchangées

`Address::equals()`, l’issuer F2, ChangeAddress et RegisterProperty n’ont pas été modifiés. Aucune migration 101 n’est créée.
