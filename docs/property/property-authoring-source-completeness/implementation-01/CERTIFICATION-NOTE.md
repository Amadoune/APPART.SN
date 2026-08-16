# F4 — Certification Note

## Verdict

**GO PROPOSÉ — F4 PROPERTY AUTHORING SOURCE COMPLETENESS IMPLEMENTATION 01 — REOPENING.**

## Levée du NO GO historique

Le NO GO initial n’était pas une défaillance du store : aucune surface ne permettait alors d’acquérir un PlaceId F1 prouvable. F4-A Authority et Implementation ont livré cette frontière et son rejeu serveur. F4 la compose désormais sans modification ni contournement.

## Capacités certifiées

- migration additive 099 et rollback ;
- huit faits présents dans le snapshot et réellement persistés ;
- sélection Geography issue de F4-A/F1 et rejeu obligatoire ;
- UUID arbitraire insuffisant ;
- AddressIntentId exclusivement serveur, stable ou renouvelé selon le couple physique ;
- snapshots historiques lisibles et incomplets ;
- checksum, replay, transaction et optimistic locking préservés ;
- aucune donnée fictive, BusinessYear, AddressId, Aggregate Property ou Promotion ;
- 99 tests, 636 assertions, PHPStan, Pint, Vite et `git diff --check` PASS.

## Gouvernance

F0–F3 et F4-A restent **GO CERTIFIÉS — FERMÉS**. F4 est proposée GO. F5, F6, F7 et RC2 Iteration 11 restent non ouvertes. Aucun staging, commit ou tag.
