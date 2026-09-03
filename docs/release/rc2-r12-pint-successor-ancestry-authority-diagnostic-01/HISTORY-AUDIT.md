# History Audit

Premier commit correctif commun : `afa494648d082a6f85825a1ad05b80d712befc8f`.

- Parent : `ebef23e12707d019eda7c1de799689ae470e1582` (R8).
- Message : `release: materialize RC2 R9 Pint correction`.
- Les trois changements sont uniquement des réordonnancements d'importations.
- Aucun comportement, déclaration, route, provider ou assertion n'est changé.
- Les trois blobs sont inchangés dans R10.
- Aucun commit successor ne les supprime intentionnellement.

Le commit R9 modifie aussi `.github/workflows/phase-5.9-reproducible-build.yml`, `build/runtime.lock.json`, `tests/Architecture/BuildCiSourceIdentityArchitectureTest.php` et `tools/release/build-release.sh`. Ces quatre fichiers portent l'identité de matérialisation R9 et ne participent pas aux corrections `ordered_imports`; ils sont exclus de la frontière candidate.
