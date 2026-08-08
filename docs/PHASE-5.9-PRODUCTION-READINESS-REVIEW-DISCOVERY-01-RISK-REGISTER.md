# Risk Register

| Risque résiduel | Impact | Preuve/traitement attendu | Criticité |
|---|---|---|---|
| rollback non exercé | indisponibilité prolongée | exercice et durée mesurée | critique |
| restore non démontré | perte de données | preuve RPO/RTO et intégrité | critique |
| dépendance externe indisponible | rupture de parcours | SLA, alerting et mode opératoire | élevée |
| secret expiré ou mal roté | compromission/indisponibilité | inventaire et rotation attestée | critique |
| angle mort de supervision | incident tardivement détecté | couverture et test d'alerte | élevée |
| capacité insuffisante | saturation | test de charge et marges | élevée |
| runbook incomplet | erreur opérationnelle | revue et exercice | élevée |
| preuve CI obsolète | release non qualifiée | rattachement exact à l'artefact | élevée |
| UAT incomplète | défaut utilisateur en production | acceptation des parcours critiques | élevée |
| autorité GO ambiguë | lancement non gouverné | RACI et décision signée | critique |

Aucun de ces risques n'est accepté implicitement par le Discovery.

