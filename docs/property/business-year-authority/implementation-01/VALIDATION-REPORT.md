# F3 — Validation Report

Date : 2026-08-14.

## Campagnes ciblées

- Unit, Architecture et Feature F3 : **PASS** — 12 tests, 42 assertions.
- Régression RealEstateCatalog ciblée : **PASS** — 46 tests, 76 assertions.
- Campagne consolidée F3 + Domain : **PASS** — 58 tests, 118 assertions.
- PHPStan ciblé : **PASS** — 0 erreur.
- Pint ciblé : **PASS**.
- `git diff --check` : **PASS**.
- PostgreSQL : **NOT_APPLICABLE**.

## Couverture

Les validations couvrent UTC, offsets positif et négatif, équivalence d’instants, frontières annuelles à la microseconde, replay stable, rejet avant autorité des instants invalides, résultat `Resolved`, binding, compatibilité Register/Update et invariants ConstructionYear.

## Gouvernance

Aucun staging, commit ou tag. F4, F5, F6 et RC2 Iteration 11 restent non ouverts.
