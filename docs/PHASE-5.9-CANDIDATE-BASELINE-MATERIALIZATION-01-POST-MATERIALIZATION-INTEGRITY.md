# Post-Materialization Integrity

Contrôles obligatoires après commit et tag :

| Contrôle | Attendu |
|---|---|
| `git status --porcelain` | vide |
| `git diff --check` | PASS |
| tag annoté | cible le commit candidat unique |
| migrations 090–091 et rollbacks | empreintes gelées inchangées |
| lockfiles | empreintes inchangées |
| clone local propre | checkout du tag possible, status vide |
| scan de secrets de l'arbre versionné | aucune occurrence détectée |

Les valeurs terminales sont rapportées après matérialisation, l'arbre du commit ne pouvant contenir son propre SHA.
