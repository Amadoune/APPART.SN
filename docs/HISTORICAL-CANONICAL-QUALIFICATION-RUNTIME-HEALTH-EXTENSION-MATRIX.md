# Historical Canonical Qualification Runtime Health Extension Matrix

| Inspection | Attendu | Effet fonctionnel |
|---|---|---|
| composant déclaré | `HistoricalCanonicalQualifier` | aucun |
| binding présent | oui | inspection du conteneur |
| implémentation compatible | `PostgreSqlHistoricalCanonicalQualifier` | construction seulement |
| dépendances constructibles | PDO et mapper présents | aucune lecture |
| appel à `qualify` | jamais | interdit |
| canonical construite | aucune | interdit |
| Runtime complet | `Healthy` | diagnostic structurel |
