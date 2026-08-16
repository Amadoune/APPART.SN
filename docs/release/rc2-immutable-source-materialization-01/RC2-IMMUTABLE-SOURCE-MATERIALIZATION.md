# RC2 Immutable Source Materialization 01

## Authorized operation

Materialize the complete certified RC2 worktree as one immutable Git candidate. Product correction, packaging, push, external CI, deployment, Phase 5.9 reopening, Iteration 12 and Search UX/API remain forbidden.

## Identity policy

- commit message: `APPART.SN RC2 candidate baseline`;
- annotated tag: `appart-sn-release-candidate-rc2`;
- parent: current RC1 HEAD `af7a61ea288ddc2177b508bd8c5bfb7c56ab1393`;
- one exact candidate tree;
- post-materialization identities recorded externally to avoid self-reference.
