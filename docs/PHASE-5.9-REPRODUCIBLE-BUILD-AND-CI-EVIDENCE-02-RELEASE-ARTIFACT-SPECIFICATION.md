# Release Artifact Specification

Format : archive USTAR non compressée déterministe `appart-release.tar`.

Inclus : code `app/**` et `src/**`, bootstrap/configuration/routes/resources versionnés, fichiers publics nécessaires, assets Vite compilés, dépendances Composer de production, migrations/rollbacks et métadonnées Composer utiles au runtime.

Exclus : `.github`, outils de build, documentation, tests, configurations PHPUnit/PHPStan/Pint, dépendances Node, sources frontend de build inutiles après compilation, dépendances Composer de développement, secrets, `.env`, caches et état runtime.

Tous les chemins sont relatifs. Les fichiers sont triés, avec mode 0644, uid/gid zéro et mtime zéro.
