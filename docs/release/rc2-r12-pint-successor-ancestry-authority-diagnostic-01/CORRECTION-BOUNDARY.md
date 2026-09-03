# Minimal Correction Boundary

Origine autoritative unique : `afa494648d082a6f85825a1ad05b80d712befc8f` (R9).

La frontière minimale proposée, non appliquée, est :

1. `bootstrap/providers.php` → blob `854bbd7e3dc481391acf5180bdd73c7792be3c89` ;
2. `routes/web.php` → blob `7b6aa7e43d7ca4b0585b86d85f303c1e68a41304` ;
3. `tests/PostgreSQL/MediaIngestionRuntime/PostgreSqlMediaIngestionRuntimeTest.php` → blob `481beaa2ff06fb4b23e4d22e5c01ec55bbd4c15e`.

Une restauration byte-for-byte suffit : les diffs R8→R9 sont exclusivement `ordered_imports`.

Sont explicitement exclus les quatre autres fichiers du commit R9 : workflow, runtime lock, test d'identité Build/CI et script de packaging. Sont également exclus `phpunit.xml`, les sept tests Architecture, l'application, les migrations, les lockfiles et tout autre fichier.

Aucun cherry-pick global de R9 n'est autorisé par ce diagnostic.
