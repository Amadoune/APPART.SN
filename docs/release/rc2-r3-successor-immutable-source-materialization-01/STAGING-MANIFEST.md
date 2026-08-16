# Staging Manifest

## Inclus — 45 chemins

### Technique, 4

- `.github/workflows/phase-5.9-reproducible-build.yml` — ordering et identité certifiés ;
- `build/runtime.lock.json` — identité RC2-R3 certifiée ;
- `tools/release/build-release.sh` — identité packaging certifiée ;
- `tests/Architecture/BuildCiSourceIdentityArchitectureTest.php` — guards identity/ordering certifiés.

### Documentation certifiée, 33

- les 20 fichiers de `docs/release/rc2-reproducible-build-frontend-prerequisite-ordering-authority-01/` ;
- les 13 fichiers de `docs/release/rc2-build-ci-frontend-prerequisite-correction-01/`.

### Preuves pré-commit, 8

- `IDENTITY-ORDERING-PREFLIGHT.md` ;
- `PREDECESSOR-EVIDENCE.md` ;
- `RC2-R3-SUCCESSOR-IMMUTABLE-SOURCE-MATERIALIZATION.md` ;
- `SECRET-AUDIT.md` ;
- `STAGED-DIFF-EVIDENCE.md` ;
- `STAGED-IDENTITY-EVIDENCE.md` ;
- `STAGING-MANIFEST.md` ;
- `WORKTREE-INVENTORY.md`.

## Exclus — 57 chemins

- 8 preuves predecessor sous `rc2-immutable-source-materialization-01` ;
- 8 preuves successor sous `rc2-successor-immutable-source-materialization-01` ;
- 20 documents post-successor sequencing evidence-only ;
- 20 preuves de la campagne Reproducible Build NO GO ;
- 1 ZIP redondant sous `docs/public-geography`.

Aucun `git add .` ou `git add -A` n'est autorisé.
