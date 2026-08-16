# Rapport de validation

Date : 2026-08-14.

## Preuves rejouables acquises

- F7-A rollback/retry : 15 tests, 149 assertions, PASS lors de sa certification terminale.
- campagne consolidée F6/Submit : 33 tests, 233 assertions, PASS lors de F7-A.
- PHPStan ciblé : PASS, 0 erreur ; Pint : PASS ; git diff --check : PASS à la clôture F7-A.

## Réouverture F7

Audit repris dans l’ordre imposé. À l’étape Property already exists, inspection de `compatible()` et `Address::equals()` : divergence canonique démontrée par le code.

Conformément au fail-fast, aucune nouvelle campagne, aucun test additionnel et aucune correction produit ne sont exécutés après cette découverte. Le contrôle documentaire `git diff --check` est effectué à la clôture.
