# Phase 5.1F — PostgreSQL Concurrency Certification

La campagne couvre :

1. deux écritures owner et le journal dans un commit unique ;
2. replay identique sans seconde exécution ;
3. replay divergent sans exécution ;
4. exception après une première écriture avec rollback intégral ;
5. deux processus PostgreSQL simultanés sur le même compte, la même opération et le même intent.

Le scénario concurrent produit exactement `Applied` et `IdempotentReplay`, une seule écriture métier et un seul intent.

Les tests exigent PostgreSQL 18.x et n'ont aucun fallback SQLite.

```powershell
php vendor/bin/phpunit --configuration phpunit.postgresql.xml `
  tests/PostgreSQL/IdentityAccessCompletion/PostgreSqlIdentityAccessAtomicTransactionTest.php
```

Résultat ciblé constaté : **4 tests, 14 assertions, PASS**.
