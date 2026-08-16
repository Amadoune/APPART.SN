# Packaging Identity Audit

`tools/release/build-release.sh` is an RC2 candidate member and requires:

- annotated `phase-5.9-baseline-candidate-r5` resolving build HEAD;
- historical source-base ancestry;
- fixed Composer/npm lock checksums.

The lock checks and historical Composer/manifest correction remain valid capabilities. The candidate identity guard rejects RC2 by design. No identity-only preflight or packaging is run because changing the guard requires a different immutable source.
