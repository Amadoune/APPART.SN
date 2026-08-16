# F7-B — Existing Property Canonical Identity Compatibility Authority 01

## Décision normative

Pour une Property existante sans preuve ledger de la commande, `AlreadyApplied` est autorisé uniquement si l’état Aggregate est exactement compatible avec l’effet canonique reconstruit par F6.

La comparaison inclut toutes les identités et tous les faits produits par la Promotion, notamment :

`existing AddressId == AddressIdentityIssuerV1(PropertyId, AddressIntentId)`.

Une différence d’AddressId est `DivergentCommand`, même lorsque GeographicPlaceId et AddressLine sont identiques.

## Frontière

Cette compatibilité est une règle d’idempotence Application propre à Promotion. Elle est volontairement plus stricte que l’égalité descriptive Domain `Address::equals()`.

`Address::equals()`, F2, `RegisterProperty`, `ChangeAddress`, le ledger et Submit restent inchangés.
