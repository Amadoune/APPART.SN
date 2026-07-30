# A-5.1-IAM-PROFILE-01 — Profile Compatibility

## 1. Matrice

| Sujet | Modèle compatible | Conclusion |
|---|---|---|
| mutabilité du nom | UserProfile versionné, Snapshot V1 seed seulement | **Compatible** |
| mutabilité email | pending claim puis activation vérifiée dans Profile | **Compatible** |
| mutabilité téléphone | pending claim puis activation vérifiée dans Profile | **Compatible** |
| revérification | challenges Profile dédiés, pas tokens V1 historiques | **Compatible** |
| maintien unicités | Claim Registry seedée avec toutes les identités 042 | **Compatible** |
| anti-takeover | réauth, possession, notifications, délai/révocation | **Compatible** |
| historique | ProfileRevision append-only et PII minimisée | **Compatible** |
| Historical Account | lecture d'amorçage, aucune mutation | **Compatible** |
| Snapshot V1 | inchangé et toujours round-trip identique | **Compatible** |
| AccountRegistry | inchangé ; nouveau Profile reader/writer distinct | **Compatible** |
| consommateurs existants | AccountId/status inchangés ; pas de rupture | **Compatible** |
| nouveaux événements | catalogue Profile V1 distinct | **Compatible** |
| Runtime gelé | composition Profile additive hors catalogue 58 | **Compatible** |
| HTTP gelé | routes Status inchangées ; routes Profile distinctes | **Compatible** |
| Outbox gelée | aucun ajout implicite ; owner Profile séparé | **Compatible** |

## 2. Incompatibilités démontrées

| Approche | Verdict | Motif |
|---|---|---|
| muter `Account` directement | interdit | Aggregate gelé |
| modifier Snapshot/migration 042 | interdit | contrat V1 et persistence gelés |
| ajouter `findByEmail` à AccountRegistry | interdit | port gelé |
| réutiliser les tokens historiques | interdit | sémantique et sécurité différentes |
| publier Profile dans Account Status V1 | interdit | Event Owner et payload gelés |
| écrire dans l'Outbox 043 sans owner/version | interdit | collision d'ownership |
| laisser deux sources canoniques indéfiniment | interdit | autorité ambiguë |

## 3. Conditions de compatibilité

La future implémentation devra avoir des migrations nouvelles et additives,
des contrats Profile propres et un cutover d'autorité certifié. Elle ne pourra
commencer qu'après ouverture de 5.1.

Aucun amendement supplémentaire des capacités 4.9 n'est requis par ce modèle.
Toute décision d'utiliser le Runtime Health ou l'Outbox générique gelés
ouvrirait toutefois un amendement d'infrastructure avant réalisation.
