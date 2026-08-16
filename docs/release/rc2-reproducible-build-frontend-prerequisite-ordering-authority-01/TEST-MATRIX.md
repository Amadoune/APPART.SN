# Test Matrix

Le futur correction gate doit vérifier :

| Cas | Attendu |
|---|---|
| Fresh checkout après restores | Manifest absent avant build |
| `npm run build` | Produit le manifest réel |
| Feature après build | Ne rencontre plus `ViteManifestNotFoundException` |
| Frontend placé après Feature | Guard FAIL |
| Manifest synthétique / `withoutVite` ajouté | Guard FAIL |
| Identité RC2-R3 cohérente | PASS |
| RC2-R2 ou R5 comme identité active | FAIL |
| Lockfiles | Inchangés |
| Workflow syntax | PASS |
| Packaging identity preflight | PASS sans artifact |

La campagne complète appartient au gate Reproducible Build rouvert après matérialisation.
