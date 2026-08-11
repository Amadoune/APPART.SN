# Validation Report

| Contrôle | Résultat |
|---|---|
| audit principal F3 | PASS — absent localement, non modifié |
| principal réel | PASS |
| credential produit par l'autorité F1 | PASS |
| rôle `publication_reviewer` réel | PASS |
| relecture `AccountRegistry` | PASS |
| read queue | Allowed |
| claim review | Allowed |
| begin review | Allowed |
| approve publication | Allowed |
| login HTTPS | HTTP 200 / succeeded |
| cookie Secure | PASS |
| cookie HttpOnly | PASS |
| cookie SameSite=Strict | PASS |
| accès `/publication-review` authentifié | HTTP 200 |
| SQL direct / fixture / bypass | ABSENT |
| modification de policy IAM | ABSENTE |

Note locale : Schannel exige `--ssl-no-revoke` avec la CA de développement, qui ne publie pas de service de révocation. TLS et la validation du certificat restent actifs.
