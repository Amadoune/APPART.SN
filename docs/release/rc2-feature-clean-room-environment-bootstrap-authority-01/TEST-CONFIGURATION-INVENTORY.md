# Test Configuration Inventory

`phpunit.xml` couvre Unit/Feature/Architecture/Foundation. `phpunit.postgresql.xml` couvre PostgreSQL. `.env.example` décrit l'application `appart_rebuild`. Le workflow fournit `APPART_TEST_PG_*` et `APPART_APPLICATION_PG_DATABASE`, mais pas les variables Laravel Feature. `PostgreSqlTestEnvironment` refuse tout fallback et protège les resets.
