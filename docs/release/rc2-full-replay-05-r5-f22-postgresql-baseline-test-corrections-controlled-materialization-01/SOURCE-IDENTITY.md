# Source identity

- immutable R5 commit: `0067a77423c2ff16b02f35d85301d45024742a77`;
- existing F22 candidate commit and materialization parent:
  `e0f76f6bc2e623125d301cd3c84ba53f3282b125`;
- existing F22 candidate tree: `97ce9eb0ab368825d2d7688af5021ba8b1d7a560`;
- dedicated branch:
  `codex/rc2-r5-f22-pg-baseline-controlled-materialization-01`;
- source worktree was clean before the two authorized test-only corrections;
- R5 and the existing F22 commit remain immutable.

The final materialization commit and tree are reported in the terminal handoff.
They cannot be embedded in their own committed contents without creating a
self-reference.
