# F1 — Certification Note

## Verdict

**GO PROPOSÉ — F1 GEOGRAPHY SELECTION FOUNDATION IMPLEMENTATION 01 — REOPENING.**

## Motif

Le NO GO initial est conservé comme qualification historique correcte : il constatait l'absence de persistance Place. F0 a depuis matérialisé et certifié cette autorité. La réouverture F1 fournit maintenant une lecture réelle, déterministe, hiérarchique, paginée et fail-closed directement sur `geography.places`.

Les cinq statuts exigés sont exécutables. Le DTO est minimal. Les Places disabled ou merged ne sont jamais proposées. Le curseur est opaque, versionné, vérifié et lié à la sélection. Le port reste read-only et distinct de `PlaceRegistry`.

## Limites certifiées

F1 n'expose aucun HTTP et n'intègre pas Property Authoring. Elle ne lit ni Search ni Projection et n'ajoute aucune migration. F2, F3, F4 et RC2 Iteration 11 restent fermées.

## Gouvernance

Aucun staging, commit ou tag n'a été réalisé.
