# F7-B — Note de certification

## Conclusion

La compatibilité ledgerless d’une Property existante exige désormais l’effet canonique F6 complet : présence d’adresse identique, AddressId F2 strict, GeographicPlaceId strict, AddressLine stricte et tous les autres faits Property inchangés.

Une Property canonique produit `AlreadyApplied` sans ledger de rattrapage ni mutation. Toute divergence produit `DivergentCommand`. Submit poursuit uniquement dans le premier cas et demeure pré-Submitted dans le second.

Les autorités F2 et Domain, le ledger, RegisterProperty, ChangeAddress et Submit restent inchangés. Aucune migration, dépendance Search/Projection, règle métier ou backfill n’a été introduit.

Les validations ciblées sont intégralement PASS, dont PostgreSQL, PHPStan, Pint et `git diff --check`.

## Verdict

**GO PROPOSÉ**

APPART.SN PROPERTY FOUNDATION
F7-B — EXISTING PROPERTY CANONICAL IDENTITY COMPATIBILITY IMPLEMENTATION 01
