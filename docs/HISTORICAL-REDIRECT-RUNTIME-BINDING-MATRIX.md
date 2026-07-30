# Historical Redirect Runtime Binding Matrix

| Service | Enregistrement | Portée | Dépendances |
|---|---|---|---|
| `HistoricalRedirectDecisionMapper` | singleton concret | Runtime | aucune |
| `PostgreSqlHistoricalRedirectResolver` | singleton concret | Runtime | `PDO`, mapper |
| `HistoricalRedirectResolver` | alias de l'adaptateur | Runtime | même instance que l'adaptateur |
| `PDO` | singleton existant | Runtime | connexion Laravel `pgsql` |

Il n'existe aucun binding vers HTTP, `PublicListingQuery`, un Repository ou un Aggregate.
