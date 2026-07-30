# Matrice de lecture des décisions Search

| Situation persistée | Résultat |
|---|---|
| aucune ligne pour le Listing | Missing |
| payload valide et checksum exact | Found |
| checksum différent | Corrupted |
| JSON invalide ou incomplet | Corrupted |
| valeur Domain invalide | Corrupted |
| état de colonne différent du payload | Corrupted |

| Relation de version à l’écriture | Résultat |
|---|---|
| aucune décision | Applied |
| version supérieure | Applied |
| version identique et payload identique | AlreadyApplied |
| version identique et payload différent | Divergent |
| version inférieure | RejectedObsolete |
