# Identity Model

| Surface | Source ancestrale | Identité candidate |
|---|---|---|
| Runtime lock | R4 `058719f8…` | tag annoté R5 |
| Workflow CI | R4 doit être ancêtre du checkout | tag R5 doit résoudre exactement vers `GITHUB_SHA` |
| Packaging | R4 doit être ancêtre de `HEAD` | tag R5 doit résoudre exactement vers `HEAD` |

Le nom du futur tag est connu avant le commit ; aucun SHA du futur commit n'est inscrit dans son propre contenu. La convention reste fermée, sans wildcard ni fallback.
