# Staging Manifest

## Inclus — chemins techniques

- `.github/workflows/phase-5.9-reproducible-build.yml` — A — bootstrap CI certifié
- `build/runtime.lock.json` — B — identité symbolique R4
- `tools/release/build-release.sh` — C — identité packaging R4
- `tools/release/check-feature-environment.php` — D — entrypoint preflight
- `tests/Support/FeatureEnvironmentPreflight.php` — D — garde preflight
- `tests/Unit/Release/FeatureEnvironmentPreflightTest.php` — E — tests fail-closed
- `tests/Architecture/BuildCiSourceIdentityArchitectureTest.php` — F — garde identité

## Inclus — groupes documentaires exacts

- Les 20 fichiers sous `docs/release/rc2-feature-clean-room-environment-bootstrap-authority-01/` — H — Authority GO
- Les 15 fichiers sous `docs/release/rc2-build-ci-feature-environment-bootstrap-correction-01/` — G — Correction GO
- Les 10 fichiers pré-commit sous ce répertoire — G — matérialisation

## Exclus

Tous les autres chemins non suivis actuels — I/J — preuves historiques externes ou artifact ZIP. `vendor`, `node_modules`, `public/build`, `.env`, logs, coverage, temp et runtime DB sont exclus.
