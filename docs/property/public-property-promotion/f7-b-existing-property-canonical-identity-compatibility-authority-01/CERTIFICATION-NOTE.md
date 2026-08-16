# Note de certification F7-B

## Décision

AddressId est une identité technique canonique F2 et doit être strictement identique lors de la comparaison ledgerless d’une Property existante. Une différence produit `DivergentCommand` même si PlaceId et AddressLine sont identiques.

`Address::equals()` reste inchangé et descriptif. La future correction est limitée à `DeterministicPromoteAuthoredPropertyV1::compatible()` et ses tests. Aucun backfill, aucune migration, aucun changement F2/Domain/Submit.

Tous les cas Address, Property, ledger, legacy et Submit sont fermés sans UNKNOWN.

## Verdict

**GO PROPOSÉ**

APPART.SN PROPERTY FOUNDATION
F7-B — EXISTING PROPERTY CANONICAL IDENTITY COMPATIBILITY AUTHORITY 01
