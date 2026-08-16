# Build Identity Status

Three current Release authorities are hard-pinned to R5:

- `build/runtime.lock.json`: `candidateTag = phase-5.9-baseline-candidate-r5`, historical source base;
- `tools/release/build-release.sh`: `EXPECTED_CANDIDATE_TAG = phase-5.9-baseline-candidate-r5`, historical source base;
- `.github/workflows/phase-5.9-reproducible-build.yml`: R5 tag trigger, R5 candidate identity and historical source base.

Status: **NOT ALIGNED WITH RC2**. Existing tooling cannot consume the RC2 tag directly without an explicitly authorized Release-tooling change.
