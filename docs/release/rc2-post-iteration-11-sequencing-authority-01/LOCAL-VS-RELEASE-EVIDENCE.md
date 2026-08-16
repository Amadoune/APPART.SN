# Local vs Release Evidence

| Evidence class | What is proven | What is not proven |
|---|---|---|
| Local runtime | RC2 end-to-end behavior and owner-store coherence on `appart.test` | Immutable source, deployability or production operations |
| Release artifact | Exact packaged content tied to commit/tag/tree/checksum | External execution or production suitability by itself |
| CI/independent reproduction | Clean restore/build/tests/package outside the authoring workspace | Target production environment and operations |
| Production | Environment, security operations, rollback, backup/DR, observability, runbooks and capacity | Not established by local or CI evidence alone |

Local PostgreSQL RC2 data is runtime evidence only. It must be preserved for audit continuity locally, excluded from source/package artifacts, and reproduced only through certified product procedures or dedicated non-production data preparation—not copied into a release artifact.
