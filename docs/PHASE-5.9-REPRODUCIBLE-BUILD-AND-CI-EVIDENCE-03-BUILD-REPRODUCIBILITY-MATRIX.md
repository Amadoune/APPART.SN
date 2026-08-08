# Build Reproducibility Matrix

| Exigence | Preuve | Statut |
|---|---|---|
| Source R2 immuable | commit et tag concordants | PASS |
| Clone propre | aucune modification | PASS |
| Runtime lié à R2 | verrou, workflow et script liés à R1 | FAIL |
| Gates qualité | non exécutées après fail-fast | BLOCKED |
| Deux packagings identiques | aucun packaging autorisé | BLOCKED |
| CI externe | aucune exécution R2 terminale | MISSING |
| Reproduction indépendante | aucune reproduction R2 | MISSING |
