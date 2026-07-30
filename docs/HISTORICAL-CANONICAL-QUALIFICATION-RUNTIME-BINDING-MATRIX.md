# Historical Canonical Qualification Runtime Binding Matrix

| Service | Enregistrement | Instance | Dépendances |
|---|---|---|---|
| `HistoricalCanonicalQualificationMapper` | singleton concret | unique | aucune |
| `PostgreSqlHistoricalCanonicalQualifier` | singleton concret | unique | PDO et mapper |
| `HistoricalCanonicalQualifier` | alias | même instance que l'adaptateur | aucune duplication |
| `PDO` | singleton Runtime existant | partagé | connexion Laravel `pgsql` |

Aucun composant HTTP, Repository, Aggregate ou service de redirection n'entre dans ce graphe.
