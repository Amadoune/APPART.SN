# Sequencing Matrix

| Gate | Current state | Prerequisite / next relation |
|---|---|---|
| RC2 immutable source | GO; tagged current candidate | Input to identity alignment |
| Build/CI identity alignment | Not executed; tooling pinned to R5 | **Sole next gate** |
| Aligned source identity | Not materialized | Required after alignment changes |
| Dependency restore | Procedure qualified on R5; RC2 run missing | Run from aligned immutable checkout |
| Packaging | Not opened / no RC2 artifact | After aligned immutable source and gates |
| Reproducible build | Not demonstrated | After deterministic packaging runs |
| External CI | Missing/not executed | After local reproducibility and official authority |
| Independent reproduction | Not demonstrated | Required before Production Readiness |
| Production Readiness | Independent NO GO | After Release and operational evidence |
| Deployment | Not opened | Requires Production Readiness GO |

Iteration 12 remains unauthorized and Search UX/API remains unopened.
