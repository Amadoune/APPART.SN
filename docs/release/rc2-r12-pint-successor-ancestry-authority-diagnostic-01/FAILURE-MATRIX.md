# Pint Failure Matrix

| File | R5 blob | F22 blob | Current blob | First corrected commit | R8 blob | R9 blob | R10 blob | Current equals old baseline | R9/R10 equal | Correction type |
|---|---|---|---|---|---|---|---|---|---|---|
| `bootstrap/providers.php` | `90769b727780d7b1461a578360a79332ce10ce49` | `90769b727780d7b1461a578360a79332ce10ce49` | `90769b727780d7b1461a578360a79332ce10ce49` | `afa49464` | `90769b727780d7b1461a578360a79332ce10ce49` | `854bbd7e3dc481391acf5180bdd73c7792be3c89` | `854bbd7e3dc481391acf5180bdd73c7792be3c89` | yes | yes | `ordered_imports` only |
| `routes/web.php` | `18ccca2c39a05ff2a5cbb3e4fd7df4ec70e6b3ef` | `18ccca2c39a05ff2a5cbb3e4fd7df4ec70e6b3ef` | `18ccca2c39a05ff2a5cbb3e4fd7df4ec70e6b3ef` | `afa49464` | `18ccca2c39a05ff2a5cbb3e4fd7df4ec70e6b3ef` | `7b6aa7e43d7ca4b0585b86d85f303c1e68a41304` | `7b6aa7e43d7ca4b0585b86d85f303c1e68a41304` | yes | yes | `ordered_imports` only |
| `tests/PostgreSQL/MediaIngestionRuntime/PostgreSqlMediaIngestionRuntimeTest.php` | `07da1c85f6ae6050e80b891ecd24da5f8b4e8274` | `07da1c85f6ae6050e80b891ecd24da5f8b4e8274` | `07da1c85f6ae6050e80b891ecd24da5f8b4e8274` | `afa49464` | `07da1c85f6ae6050e80b891ecd24da5f8b4e8274` | `481beaa2ff06fb4b23e4d22e5c01ec55bbd4c15e` | `481beaa2ff06fb4b23e4d22e5c01ec55bbd4c15e` | yes | yes | `ordered_imports` only |

R6, R7 et R8 portent également les anciens blobs. Les trois résultats ne sont donc pas introduits par les corrections APP_URL ou Architecture du worktree actuel.
