# Rapport de validation

Date d’exécution : 2026-08-14. PHP 8.5.8, PostgreSQL local productif de test.

## Campagnes F6

- Unit, Feature/Composition, Architecture et PostgreSQL Promotion + Submit : **23 tests, 122 assertions, PASS**. La campagne consolidée finale couvre promotion réelle, replay, divergence, owner, version, complétude, Geography, rollback, migration et Submit.
- Submit PostgreSQL négatif : PASS ; promotion refusée, Listing conservé `Draft`, aucune Property créée.

## Régressions ciblées

- F2 Address Identity, F3 Business Year, F4 Source Completeness, F5-A Geographic Place Catalog et stores PostgreSQL impactés : **56 tests, 338 assertions, PASS**.
- Listing Submit / HTTP Authoring ciblés : inclus dans la campagne consolidée F6, PASS.

## Qualité statique

- PHPStan ciblé : PASS, 0 erreur.
- Pint ciblé : PASS.
- `git diff --check` : PASS.
- Vite : non requis ; aucun comportement frontend nouveau, seul le jeton de version Authoring déjà détenu par le workspace est transporté au Submit.
