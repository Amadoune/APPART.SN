# Note de certification

## Verdict

**NO GO PROPOSÉ**

## Cause racine unique

La compatibilité F6 d’une Property existante utilise `Address::equals()`, qui ignore AddressId. Une Address non canonique peut ainsi être considérée compatible malgré un identifiant différent de l’UUIDv5 F2 attendu.

F7 exige qu’un Aggregate existant incompatible retourne `DivergentCommand` et que l’AddressId persisté corresponde exactement à F2. Ces deux critères ne peuvent être certifiés.

Le blocker transactionnel historique est fermé. Aucun code, test, migration, staging, commit ou tag n’est produit dans cette réouverture.

APPART.SN PROPERTY FOUNDATION
F7 — PUBLIC PROPERTY PROMOTION RECERTIFICATION — REOPENING 01
