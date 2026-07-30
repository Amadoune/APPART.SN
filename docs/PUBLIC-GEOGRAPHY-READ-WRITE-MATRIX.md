# Matrice Public Geography

| Lecture | Résultat |
|---|---|
| aucune ligne | Missing |
| contenu et révision cohérents | Found |
| payload ou checksum incohérent | Corrupted |

| Écriture | Résultat |
|---|---|
| première ou version supérieure | Applied |
| même version, contenu et causalité identiques | AlreadyApplied |
| même version, fait différent | Divergent |
| version inférieure | RejectedObsolete |
