# Matrice Content/SEO Snapshot

| Situation | Lecture |
|---|---|
| aucune ligne | Missing |
| payload valide et checksum exact | Found |
| checksum différent | Corrupted |
| JSON ou Value Object invalide | Corrupted |

| Écriture | Résultat |
|---|---|
| première version ou version supérieure | Applied |
| version et payload identiques | AlreadyApplied |
| même version, payload différent | Divergent |
| version inférieure | RejectedObsolete |
