# Matrice de lecture Active Generation

| État observé | Résultat | Génération exposée |
|---|---|---|
| aucune ligne Active | `Missing` | non |
| exactement une ligne Active valide | `Found` | oui |
| plusieurs lignes Active | `Corrupted` | non |
| identité ou état non mappable | `Corrupted` | non |

Le reader n'interprète jamais une Candidate ou une Retired comme Active et ne sélectionne jamais une ligne parmi plusieurs.
