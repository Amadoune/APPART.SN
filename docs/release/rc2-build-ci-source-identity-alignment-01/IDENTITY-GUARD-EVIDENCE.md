# Identity Guard Evidence

Current guards require the R5 annotated tag to resolve build HEAD and the historical source base to be an ancestor. The RC2 tag/commit/tree are not accepted by these embedded controls.

No guard test is added or modified: doing so would itself alter candidate source. R5 rejection, mismatch-tag rejection and tree-specific RC2 guards belong to a future alignment implementation only after a new materialization sequence is explicitly authorized.
