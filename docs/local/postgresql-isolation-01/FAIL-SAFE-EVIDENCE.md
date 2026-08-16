# Fail-safe Evidence

`PostgreSqlTestEnvironment::connection()` et `reset()` vérifient désormais :

1. `current_database()` est disponible ;
2. son nom est explicitement test-only ;
3. l'identité de la base applicative est déclarée ;
4. les deux noms diffèrent.

Tout défaut lève `RuntimeException` avant le premier `TRUNCATE`, sans fallback.

Preuve réelle sur la configuration historique : la campagne Resume PostgreSQL s'arrête avec `PostgreSQL tests refuse to use the local application database`, 0 assertion, avant reset.

Preuve unitaire : 4 tests, 7 assertions, PASS, couvrant séparation valide, collision, nom non-test et identité applicative absente.
