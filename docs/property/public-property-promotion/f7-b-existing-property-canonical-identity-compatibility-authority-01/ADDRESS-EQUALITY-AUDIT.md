# Audit de l’égalité Address

## Sémantique actuelle

`Address::equals()` compare uniquement :

- `GeographicPlaceId` par son égalité de valeur ;
- `AddressLine` par son égalité de valeur.

Elle ne compare pas `AddressId`. Il s’agit d’une égalité descriptive de l’adresse immobilière, non d’une égalité complète d’identité technique.

## Consumers inventoriés

- `Property::changeAddress()` : refuse une modification lorsque les faits descriptifs sont inchangés ;
- `DeterministicPromoteAuthoredPropertyV1::compatible()` : l’utilise actuellement pour la convergence Promotion ;
- tests Domain/use cases : vérification des faits Address et de l’adresse précédente.

Modifier cette méthode autoriserait dans `ChangeAddress` une modification d’AddressId seule sans changement Place/Line. Cet impact Domain est hors périmètre et injustifié.
