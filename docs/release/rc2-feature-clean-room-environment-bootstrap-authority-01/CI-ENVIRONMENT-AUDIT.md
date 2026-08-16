# CI Environment Audit

Le workflow crée PostgreSQL 18.4 `appart_test` et fournit `APPART_TEST_PG_*`, mais omet `DB_*` et `APP_KEY` avant Feature. External CI reproduirait donc le gap. La correction doit rendre cette transmission explicite dans le workflow/source.
