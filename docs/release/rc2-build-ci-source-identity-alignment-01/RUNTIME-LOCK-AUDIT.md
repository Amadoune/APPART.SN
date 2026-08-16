# Runtime Lock Audit

`build/runtime.lock.json` is included in the RC2 tree and currently declares:

- `candidateTag`: `phase-5.9-baseline-candidate-r5`;
- `sourceBaseCommit`: `058719f8aa154466056299b8c26bd7d51f944127`;
- identity policy: annotated candidate tag resolves HEAD and source base is ancestor.

Runtime versions remain qualified and need no change. Candidate identity needs alignment, but editing this candidate member would create a new source identity and is forbidden here.
