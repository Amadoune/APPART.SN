# Evidence Matrix

| Domaine | Preuve terminale attendue | Critère GO | Critère NO GO |
|---|---|---|---|
| Release | manifeste immuable, versions et checksums | artefacts traçables | artefact ambigu ou mutable |
| Qualité | Unit, Architecture, Feature, PostgreSQL, PHPStan, Pint, diff-check | toutes les gates retenues PASS | résultat absent, non terminal ou FAIL |
| Migration | inventaire, ordre, durée, compatibilité et rollback testé | chemin déterministe approuvé | rollback absent ou migration non bornée |
| Backup/restore | restauration chronométrée et intégrité vérifiée | RPO/RTO respectés | restauration non démontrée |
| Observabilité | dashboards, health checks, alertes et owners | parcours critiques couverts | angle mort critique |
| Sécurité | revue secrets, vulnérabilités, accès et audit | aucune réserve bloquante | risque critique ouvert |
| Exploitation | runbooks, astreinte, escalade et communication | responsables disponibles | responsabilité non assignée |
| Capacité | charge, quotas, files et marges | seuils et alertes qualifiés | saturation probable non maîtrisée |
| UAT/E2E | scénarios critiques et acceptation traçable | acceptation complète | parcours critique non validé |
| Rollback | déclencheurs, autorité, durée et validation post-retour | exercice concluant | retour arrière impraticable |

Cette matrice qualifie les preuves futures ; elle ne les fabrique pas et n'ouvre aucune implémentation.

