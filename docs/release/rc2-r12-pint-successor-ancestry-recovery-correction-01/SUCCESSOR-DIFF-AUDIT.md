# Successor Diff Audit

Delta fonctionnel autorisé :

- `phpunit.xml` : une insertion APP_URL ;
- sept fichiers `tests/Architecture/` : blobs R8/R9/R10 ;
- `bootstrap/providers.php`, `routes/web.php` et `PostgreSqlMediaIngestionRuntimeTest.php` : blobs R9/R10.

Total : onze fichiers fonctionnels suivis.

Aucun code sous `app/` ou `src/`, SQL, workflow, runtime lock, script de packaging, lockfile ou configuration Pint n'est modifié. Les documents probatoires sont séparés du delta fonctionnel.
