# Aggregate/Outbox Atomicity Result Matrix

| Scénario | Résultat attendu | Preuve |
|---|---|---|
| Mutation puis append | commit commun | Listing et message Outbox présents |
| Échec avant append | rollback Aggregate | aucune ligne Listing ou Outbox |
| Échec après append avant commit | rollback commun | aucune ligne Listing ou Outbox |
| Repository Laravel | participant injecté | inspection du graphe conteneur |
| Connexion | instance PDO unique | inspection transaction, participant et Repositories |
| Transaction imbriquée | aucune | scénarios atomiques exécutés sans erreur PDO |
| Runtime Health | `Healthy` | test Runtime Binding |
| Worker 3.6H | résolu | test Runtime Binding |
