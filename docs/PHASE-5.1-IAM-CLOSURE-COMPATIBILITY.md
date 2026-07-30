# A-5.1-IAM-CLOSURE-01 — Closure Compatibility

## 1. Matrice

| Sujet | Modèle | Conclusion |
|---|---|---|
| Closed | état d'une autorité Closure additive | **Compatible** |
| Deleted | résultat différé, interdit dans le close transactionnel | **Compatible comme politique différée** |
| Anonymized | processus Privacy/Erasure distinct | **Amendement supplémentaire requis** |
| différence Suspended | autorités, finalités et transitions séparées | **Compatible** |
| réouverture | possible avant erasure et sans contourner suspension | **Compatible** |
| rétention | classification/hold/échéance dans Closure policy | **Compatible** |
| anonymisation | non simulée ; A-5.1-IAM-ERASURE-01 | **Amendement supplémentaire requis** |
| suppression logique | Closed + masquage/deny | **Compatible** |
| suppression physique | seulement après Erasure certification | **Amendement supplémentaire requis** |
| invalidation sessions | commande obligatoire vers Session owner | **Compatible** |
| impacts cross-domain | événements/readers, décisions chez chaque owner | **Compatible** |
| Listing | ID conservé, mutation future refusée | **Compatible** |
| Lead | preuves conservées selon owner/rétention | **Compatible** |
| Reservation | obligations en cours conservées | **Compatible** |
| Favorites | accès bloqué, purge chez Favorites | **Compatible** |
| Audit | ActorId/preuve conservés, PII minimisée | **Compatible** |
| événements | catalogue Closure distinct | **Compatible** |
| Snapshot V1 | aucune mutation au close | **Compatible** |
| migrations 041–043 | inchangées | **Compatible** |
| Runtime | tranche additive hors 58 | **Compatible** |
| HTTP | routes Closure distinctes | **Compatible** |
| Outbox | owner Closure séparé | **Compatible** |

## 2. Portée du GO proposé

Les trois mentions « amendement supplémentaire requis » concernent
l'effacement/anonymisation physique, pas la capacité métier Closed.

Le GO Closure autorise conceptuellement :

- demander et confirmer une fermeture ;
- invalider l'accès et les sessions ;
- préserver les références et appliquer la rétention ;
- réouvrir avant erasure si la policy le permet.

Il n'autorise pas :

- modifier ou supprimer Snapshot V1 ;
- promettre un droit à l'effacement déjà implémenté ;
- cascade-delete Listing/Lead/Reservation/Audit ;
- ajouter Closure à Event/Outbox/Runtime/HTTP Account Status.

## 3. Approches incompatibles

| Approche | Motif |
|---|---|
| `suspend()` comme fermeture | finalité et rétention absentes |
| DELETE `identity_access.accounts` | cascade, preuves et références non qualifiées |
| booléen `closed` dans migration 042 | mutation gelée |
| nouvel état dans Account Status | workflow/Event V1/HTTP gelés |
| anonymisation par valeur factice partagée | unicités, réidentification et corruption |
| réouverture réactivant Status | contourne l'autorité administrative |
