# Artifact Integrity Evidence

Le build produit :

- `appart-release.tar` ;
- `artifact-sha256.txt` ;
- `tree-sha256.txt` ;
- `tree-root-sha256.txt` ;
- `release-manifest.json`.

Chaque manifeste lie baseline, tag, commit Build/CI, runtimes, lockfiles, artefact, arbre, migrations, gates et identité CI.

Valeurs terminales : `BLOCKED`. Le packaging n'est pas exécuté après l'échec Architecture afin de ne pas fabriquer un artefact présenté à tort comme certifiant.
