# Matrice Public Media

## Lecture

| État durable | Résultat | Décision exposée |
|---|---|---|
| aucune ligne | `Missing` | non |
| payload et révision cohérents | `Found` | oui |
| payload, type ou checksum invalide | `Corrupted` | non |

## Écriture

| Situation | Résultat | Effet durable |
|---|---|---|
| aucune version stable | `Applied` | insertion atomique |
| version candidate plus récente | `Applied` | remplacement atomique |
| même version, contenu et causalité | `AlreadyApplied` | aucun double effet |
| version candidate plus ancienne | `RejectedObsolete` | aucun effet |
| même version, contenu ou causalité différent | `Divergent` | aucun effet |

## Atomicité

La révision et le contenu ne forment qu'un enregistrement. La contrainte d'égalité des checksums et la transaction rendent impossible un état partiellement publié.
