# F7-B — Implémentation de la compatibilité canonique

## Périmètre

La correction produit est limitée à `DeterministicPromoteAuthoredPropertyV1::compatible()`.

La comparaison d’adresse délègue désormais à une matrice explicite :

| Adresse existante | Adresse attendue | Résultat |
|---|---|---|
| absente | absente | compatible |
| présente | absente | divergente |
| absente | présente | divergente |
| présente | présente | `AddressId` identique et `Address::equals()` vrai |

L’`AddressId` attendu demeure celui assemblé par F6 via `AddressIdentityIssuerV1`. Aucune identité n’est recalculée dans la comparaison et l’identité existante ne devient pas une autorité.

## Invariants préservés

- `Address::equals()` conserve sa sémantique descriptive PlaceId + AddressLine.
- Les comparaisons Property existantes restent strictes : identifiant, version initiale, référence, type, surface, pièces, salles de bain et année de construction.
- Le ledger est consulté avant la compatibilité avec l’Aggregate existant.
- Aucun ledger de rattrapage, backfill ou changement de l’Aggregate n’est effectué.
- Aucun changement de Submit, F2, RegisterProperty, ChangeAddress, store, Provider, SQL ou migration.

## Résultats fermés

- Aggregate ledgerless exactement canonique : `AlreadyApplied`.
- Toute divergence canonique, dont `AddressId` seul : `DivergentCommand`.
- Replay ledger identique et checksum divergent : comportement antérieur conservé.
