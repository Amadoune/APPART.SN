# Clean-room Build Procedure

1. Créer un répertoire vide.
2. Cloner le repository et checkout l'identité Build/CI annotée.
3. Vérifier l'ascendance de `1337e225…`, le tag baseline et un status propre.
4. Installer exactement les runtimes de `build/runtime.lock.json`.
5. Exécuter `composer install` et `npm ci` depuis les lockfiles.
6. Exécuter toutes les gates du workflow.
7. Exécuter `npm run build`.
8. Exécuter `bash tools/release/build-release.sh`.
9. Conserver `appart-release.tar`, les hashes d'arbre/artefact et `release-manifest.json`.

Le script reconstruit une racine release depuis `git archive`; il ne consomme aucun `vendor`, `node_modules`, `public/build`, `storage`, cache ou `.env` préexistant.
