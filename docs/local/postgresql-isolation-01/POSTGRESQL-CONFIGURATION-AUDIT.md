# PostgreSQL Configuration Audit

## Sources

| Source | Rôle | Valeur/convention |
|---|---|---|
| `.env` | application locale | historiquement `appart_test` |
| `.env.example` | convention application | `appart_rebuild` |
| `.env.postgresql.example` | suites PostgreSQL | `APPART_TEST_PG_DSN` vers `appart_test` |
| `phpunit.postgresql.xml` | sélection de campagne | ne définit aucune connexion |
| environnement opérateur | credentials de test | `APPART_TEST_PG_*` |
| workflow Phase 5.9 | PostgreSQL CI éphémère | `appart_test` |
| `PostgreSqlTestEnvironment` | migrations et reset | connexion explicite, nombreuses opérations `TRUNCATE` |

## Frontières destructives

`PostgreSqlTestEnvironment::reset()` est l'autorité centrale de nettoyage et tronque les stores métier, ledgers, projections, sessions et référentiels. Plusieurs tests effectuent aussi des `TRUNCATE` ciblés après obtention de cette même connexion. Les harnesses et workers PostgreSQL passent tous par `PostgreSqlTestEnvironment::connection()` : la garde placée à cette entrée les couvre avant toute opération propre au test.

L'inventaire recense 178 fichiers de tests PostgreSQL/Feature contenant un reset central ou un `TRUNCATE` ciblé. Aucun de ces tests PostgreSQL ne construit une seconde connexion PostgreSQL hors du helper ; les autres `new PDO` relevés dans Feature ciblent uniquement `sqlite::memory:`.

La cause historique est la collision `DB_DATABASE=appart_test` et `APPART_TEST_PG_DSN ... dbname=appart_test`.

## Reopening 02 — découverte administrative

- session disponible : `current_user=session_user=appart_test` ;
- `appart_test` : login autorisé, ni superuser ni CREATEDB ;
- `postgres` : rôle administratif présent, superuser et CREATEDB ;
- aucune variable d'environnement administrative reconnue ;
- aucun fichier `pgpass.conf` ;
- aucun credential Windows PostgreSQL/pgAdmin référencé ;
- pgAdmin contient une définition locale PostgreSQL 18 (`localhost:5432`, maintenance DB `postgres`, username `postgres`) mais `save_password` est absent/null.

Cette configuration ne constitue pas une authentification administrative utilisable. Aucun secret stocké n'a été lu ou exposé.
