# Historical Identity Audit

Active historical references:

- `build/runtime.lock.json`: R5 candidate tag and historical source base;
- `.github/workflows/phase-5.9-reproducible-build.yml`: R5 trigger, candidate ID/tag and source base;
- `tools/release/build-release.sh`: expected R5 tag and source base.

All three files exist in both R5 and RC2 candidate trees. R5 commit `9801d9e` modified all three and materialized them together before the R5 tag was created. Historical proofs and tags remain untouched.
