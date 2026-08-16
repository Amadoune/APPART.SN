# Immutable Materialization Requirement

Historical R1–R5 and RC1 conventions require, in order:

1. exact candidate path inventory and exclusions;
2. migrations/providers/bindings/lockfile consistency checks;
3. whitespace validation of the exact index;
4. one candidate commit with an exact tree identity;
5. one annotated candidate tag resolving to that commit;
6. a baseline manifest without cryptographic self-reference;
7. an external post-materialization execution report carrying commit/tag/tree identities;
8. a clean final worktree.

These requirements precede new RC2 packaging and CI. They are documented, not executed, here.
