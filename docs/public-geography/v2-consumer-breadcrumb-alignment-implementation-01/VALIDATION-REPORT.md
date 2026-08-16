# Validation report

Périmètre ciblé exécuté : Public Geography DTO/mapper, ContentSeo policy, Certified Projection Source, Projection DTO, read model, page publique et Architecture.

Résultats terminaux :

- Unit/Feature/Architecture/intégration ciblés : 54 tests PASS, 213 assertions;
- PostgreSQL Public Geography ciblé : 5 tests PASS, 13 assertions;
- PHPStan complet : PASS, 0 erreur;
- Pint ciblé : PASS;
- Vite : PASS (avertissement optionnel `fontaine`, non bloquant);
- `git diff --check` : PASS;
- `git diff --cached --check` : PASS.

PHPStan avait initialement signalé une annotation de type iterable incomplète; elle a été corrigée à la source sans suppression ni baseline.

Le terminal RC2 `c3120000-0000-4000-8000-000000000003` n’a pas été matérialisé. Le futur breadcrumb `Senegal → Dakar Region → Dakar` est représentable et consommable sans URL par les adapters testés.
