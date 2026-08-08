# Phase 5.8C — Experience & Acceptance — Risk Register

| Risque | Impact | Maîtrise candidate |
|---|---|---|
| Owner transverse devenant autorité métier | Décisions déplacées | Limiter l'owner aux critères et preuves d'acceptation |
| Critère UX ambigu | UAT non reproductible | Critères observables, fermés et traçables |
| Accessibilité traitée tardivement | Exclusion et reprise coûteuse | Gates dédiées dès le futur RC |
| Responsive non déterministe | Parcours incohérents | Matrice viewport/entrée/version explicite |
| E2E couplé aux données internes | Frontières violées | Utiliser uniquement surfaces publiques autorisées |
| Performance confondue avec disponibilité | Faux verdict de readiness | Séparer expérience perçue et ReliabilityOperations |
| Internationalisation ouverte implicitement | Périmètre incontrôlé | Maintenir i18n comme aptitude à qualifier |
| UAT exposant PII ou secrets | Risque Security/Privacy | Jeux minimaux, synthétiques et contrôlés |
| RC mutable | Preuves non reproductibles | Baseline candidate identifiée et immuable |
| Production Readiness auto-déclarée | Mise en production prématurée | Approbations nominatives et preuves multi-owner |

