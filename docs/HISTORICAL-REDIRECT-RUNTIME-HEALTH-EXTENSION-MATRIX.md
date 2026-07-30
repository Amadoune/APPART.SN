# Historical Redirect Runtime Health Extension Matrix

| Vérification | Attendu | Effet autorisé |
|---|---|---|
| composant déclaré | `HistoricalRedirectResolver` | aucun |
| contrat lié | oui | inspection du conteneur |
| implémentation compatible | `PostgreSqlHistoricalRedirectResolver` | construction paresseuse du graphe |
| dépendances constructibles | PDO et mapper présents | aucune requête |
| canonical résolue | jamais | interdit |
| statut du graphe complet | `Healthy` | diagnostic de composition uniquement |

Une absence ou incompatibilité rendrait le Runtime indisponible selon les règles Runtime Health existantes. Aucun diagnostic ne tente de réparer ou remplacer le composant.
