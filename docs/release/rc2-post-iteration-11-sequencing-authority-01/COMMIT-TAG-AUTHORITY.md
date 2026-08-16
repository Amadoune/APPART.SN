# Commit / Tag Authority

No staging, commit or tag is authorized in this gate.

Historical convention establishes that immutable candidates require an exact source inventory, a candidate commit, an annotated tag resolving to that commit, an exact tree identity and an external post-materialization execution report where self-reference would otherwise occur.

RC2 will require those properties before packaging/CI, but exact commit message, tag name, manifest and scope must be authorized by a future RC2 Release Materialization authority. They are not invented here.
