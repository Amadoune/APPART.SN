# Release Manifest V1

Schéma matérialisé : `appart.release-manifest.v1`.

Champs : `releaseCandidateId`, `candidateCommitSha`, `candidateTag`, `buildCommitSha`, `buildDateUtc`, versions PHP/Composer/Node/npm, identité d'environnement, hashes des lockfiles, hashes archive/arbre, nombre de fichiers, migrations, identités CI et verdict des gates.

Le manifeste est produit hors archive afin que sa date et l'identité de reproduction ne rendent pas l'archive applicative divergente. Aucun secret n'y est écrit.

Instance terminale : `BLOCKED` par la gate Architecture ; aucun manifeste PASS n'est émis.
