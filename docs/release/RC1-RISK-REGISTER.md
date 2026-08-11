# RC1 Risk Register

## Critical — bloquants RC1

| ID | Risque | Preuve | Condition de fermeture |
|---|---|---|---|
| RC1-C01 | état produit non reproductible depuis Git | worktree fortement modifié/non suivi ; R5 antérieur | matérialiser une baseline candidate immuable contenant exactement l'état audité |
| RC1-C02 | absence de chaîne de confiance externe | remote/owner/URL/principal/Actions/custody/reproduction indépendante manquants | décision d'autorité externe puis CI et reproduction indépendantes sur le candidat exact |
| RC1-C03 | publication bout-en-bout non certifiée | P08 arrêté sur queue vide | produire un vrai Submitted puis démontrer Claim→Published→Projection→Search→HTTP 200 |
| RC1-C04 | impossibilité d'exploiter et restaurer raisonnablement la release | absence de cible, rollout/rollback exercé, backup/restore/DR et monitoring | qualifier l'environnement et exécuter les exercices opérationnels sur le candidat |

## Major

| ID | Risque | Traitement attendu avant production |
|---|---|---|
| RC1-M01 | configuration exemple orientée local/debug | fournir un profil production fail-closed et une matrice des variables/secrets |
| RC1-M02 | stockage Media local sans durabilité attestée | qualifier backend, sauvegarde, restauration et permissions production |
| RC1-M03 | absence de preuve de capacité/charge | fixer objectifs et mesurer marges des chemins critiques |
| RC1-M04 | accessibilité non outillée sur source RC | exécuter une validation dédiée sur le candidat immuable |
| RC1-M05 | clés IAM pouvant retomber sur `APP_KEY` sans preuve de séparation production | démontrer rotation, séparation et custody des clés production |

## Minor

| ID | Risque | Observation |
|---|---|---|
| RC1-m01 | rétention d'artefacts CI configurée à 30 jours | durée potentiellement insuffisante pour l'audit release |
| RC1-m02 | documentation historique volumineuse avec nombreux NO GO clos | nécessite un registre normatif courant pour éviter l'ambiguïté |

## Observations

- Les preuves locales R5 sont exceptionnellement complètes jusqu'à la clean-room.
- Les lockfiles et le pinning runtime réduisent fortement le risque de dérive des dépendances.
- Les résultats fermés, le fail-closed IAM et l'absence de SQL UI sont des points forts.
