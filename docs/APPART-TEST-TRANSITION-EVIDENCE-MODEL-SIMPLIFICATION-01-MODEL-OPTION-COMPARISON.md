# Model Option Comparison

| Option | Compatibilité | Complexité | Décision |
|---|---|---:|---|
| A. Reason nullable contrôlé par la policy | additive, anciennes valeurs intactes | faible | retenue |
| B. Variantes typées par transition | possible | forte, duplication du modèle événement/révision | rejetée |
| C. Factory owner-scoped | ne résout pas le schéma ni les types persistés | moyenne | rejetée |
| D. Valeur neutre persistée | fausse donnée métier | faible | interdite |
| E. Mécanisme existant | aucun mécanisme équivalent identifié | — | indisponible |

Le choix A est le plus petit changement cohérent. `null` exprime réellement l'absence de justification, tandis qu'une raison existante reste représentable sans conversion.
