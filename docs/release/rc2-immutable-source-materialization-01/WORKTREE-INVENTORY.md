# Worktree Inventory

Every modified/untracked path is classified in `STAGING-MANIFEST.md`.

| Category | Scope | Decision |
|---|---|---|
| A | RC2 product source: app, src, routes, resources, bootstrap | Include |
| B | RC2 tests | Include |
| C | Product/Foundation documentation and evidence | Include |
| D | Release engineering, workflow and release documentation | Include |
| E | Versionable local-environment template | Include |
| F | Temporary/harness | None found |
| G | Generated/artifact | One duplicate ZIP; exclude |
| H | Unknown | None |

Database contents, local `.env`, vendor, node_modules, public build output and runtime storage are ignored and absent from the candidate.
