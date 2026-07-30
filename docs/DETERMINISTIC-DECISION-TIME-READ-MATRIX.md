# Matrice de lecture `decisionAt`

| État du snapshot propriétaire | Résultat | `decisionAt` exposé |
|---|---|---|
| snapshot absent | `Missing` | non |
| snapshot valide et checksum conforme | `Found` | valeur originale exacte |
| identité, payload, date ou checksum invalide | `Corrupted` | non |

Il n'existe ni valeur par défaut, ni lecture d'horloge, ni conversion vers un temps de rebuild ou de replay.
