# Geographic Place Addressability Authority 01

## Décision normative

Pour une `Address` Property, l’adressabilité d’une Place dépend exclusivement de son `PlaceType` courant :

- `City`, `District`, `Neighborhood` : **Addressable** ;
- `Country`, `Region`, `Department` : **NotAddressable**.

Une City est une localisation finale légitime même lorsqu’un District ou un Neighborhood existe ou pourrait exister. La policy n’impose jamais la granularité la plus fine et ne suppose pas que toutes les branches Geography possèdent les mêmes niveaux.

## Owner

RealEstateCatalog Domain est l’owner unique de la règle « Place utilisable comme localisation d’une Address Property ». Geography reste owner des types, de la hiérarchie et du lifecycle Place ; il ne reçoit aucune règle spécifique à Property.

## Portée

La règle est indépendante de `PropertyType`. Elle est consultée uniquement lorsqu’une `Address` existe. `PropertyTypePolicy` reste seule responsable de décider si cette Address est requise.

## Intégration future

Le catalogue appliquera l’ordre fermé : existence, merge, enabled, addressability. Seul un Place existant, non merged, enabled et addressable produit `Usable`.

Cette Authority ne modifie aucun code et autorise uniquement la completion documentaire du blueprint F5-A.

**Verdict : GO PROPOSÉ.**
