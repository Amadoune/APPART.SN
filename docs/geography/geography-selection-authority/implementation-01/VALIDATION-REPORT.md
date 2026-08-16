# F1 — Validation Report

Date : 2026-08-14.

## Campagnes ciblées

- Unit + Architecture + Feature F1 : PASS — 7 tests, 24 assertions.
- PostgreSQL F1 : PASS — 2 tests, 7 assertions.
- Régression F0 ciblée : PASS — 37 tests, 90 assertions.
- Campagne consolidée F1 + régression F0 : PASS — 46 tests, 121 assertions.
- PHPStan ciblé : PASS — 0 erreur.
- Pint ciblé : PASS.
- `git diff --check` : PASS.

## Couverture

Les validations couvrent les cinq statuts, les paramètres invalides, les racines, la hiérarchie, l'ordre normalisé, la pagination, l'intégrité et le scope du curseur, les exclusions enabled/merged, le parent manquant, le binding et les frontières d'architecture.

## PostgreSQL

La preuve utilise PostgreSQL 18 et la migration F0 `098_geography_places.sql`. Aucune migration F1 n'a été nécessaire.

## Gouvernance

Aucun staging, commit ou tag. Les campagnes sont limitées aux surfaces F1 et F0 réellement impactées.
