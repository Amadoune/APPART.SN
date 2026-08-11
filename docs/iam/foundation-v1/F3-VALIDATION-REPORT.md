# F3 — Validation Report

| Validation | Résultat | Justification |
|---|---|---|
| RegisterAccount | PASS | principal durable créé via le use case certifié |
| CredentialHashAuthorityV1 | PASS | hash Argon2id produit par F1 |
| AccountRegistry | PASS | persistence réalisée sans SQL direct |
| HTTPS Home | PASS | navigation navigateur réelle vers `https://appart.test/` |
| présence du Login Web | FAIL | bouton informatif, aucun formulaire/script |
| transport CSRF public | MISSING | token absent de la Home |
| Idempotency-Key navigateur | BLOCKED | aucune composition Web disponible |
| Login/Cookie/Reload/Workspace | BLOCKED | première porte rouge : entrée Login absente |
| Logout/Reload refusé/Rotation | BLOCKED | aucune session navigateur préalable |
| Unit/Feature/Architecture/PostgreSQL/PHPStan/Pint | NOT_RUN | aucun code F3 créé ou modifié après la porte rouge |
| `git diff --check` | PASS | aucune erreur whitespace |

La règle fail-fast est appliquée. Les PASS F1/F2 ne sont pas recyclés comme preuve d'une session navigateur F3.

Le principal local demeure disponible pour une future démonstration après autorisation explicite d'une surface IAM Web Entry. Aucun staging, commit ou tag n'est effectué.
