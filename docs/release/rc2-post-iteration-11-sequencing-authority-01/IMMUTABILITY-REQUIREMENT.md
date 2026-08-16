# Immutability Requirement

Before RC2 packaging or CI, sources must be:

- exhaustively inventoried;
- secret/local-runtime-data excluded;
- commit-identified;
- tree-identified;
- annotated-tag identified;
- manifest/checksum identified;
- clean in a dedicated clone.

Artifacts must identify that same commit/tag/tree and carry complete manifests and SHA-256 checksums. These requirements are mandatory but not executed until a future materialization gate is explicitly opened.
