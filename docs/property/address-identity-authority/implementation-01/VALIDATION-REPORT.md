# F2 — Validation Report

Date : 2026-08-14.

## Campagnes ciblées

- Unit, Architecture et Feature F2 : **PASS** — 7 tests, 28 assertions.
- Régression RealEstateCatalog Domain ciblée : **PASS** — 46 tests, 76 assertions.
- Campagne consolidée F2 + régression Domain : **PASS** — 53 tests, 104 assertions.
- PHPStan ciblé : **PASS** — 0 erreur.
- Pint ciblé : **PASS**.
- `git diff --check` : **PASS**.
- PostgreSQL : **NOT_APPLICABLE**.

## Couverture

- PropertyId et AddressIntentId valides ;
- rejet d’un AddressIntentId non UUID ;
- UUIDv5 exact et format canonique ;
- vecteur de référence littéral ;
- stabilité multi-appels, multi-instances et reconstruction ;
- distinction nouvelle intention et autre Property ;
- acceptation par `AddressId` ;
- absence de temps, aléa, persistence et dépendances externes ;
- résolution réelle du binding.

## Gouvernance

Aucun staging, commit ou tag. F3, F4, F5 et RC2 Iteration 11 ne sont pas ouverts.
