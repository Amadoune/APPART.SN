# Runtime Pinning Evidence

| Contrôle | Preuve observée | Statut |
|---|---|---|
| Commit R2 | `5b1d0e647d1f74629b5f7e99e6f9d7e31941e988` | PASS |
| Tag annoté R2 | `phase-5.9-baseline-candidate-r2` pointe sur R2 | PASS |
| Source du verrou | `build/runtime.lock.json` désigne R1 `1337e225c63e6a3e25c5926f7c4fbddb4ba24da7` / `phase-5.9-baseline-candidate` | FAIL |
| Workflow | `.github/workflows/phase-5.9-reproducible-build.yml` fixe encore R1 | FAIL |
| Packaging | `tools/release/build-release.sh` fixe encore R1 | FAIL |

Les versions Runtime restent explicitement verrouillées, mais leur identité source diverge de la source R2 obligatoire. Aucun fichier technique ni lockfile n'est modifié pendant ce jalon.
