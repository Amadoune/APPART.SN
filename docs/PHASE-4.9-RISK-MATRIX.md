# Phase 4.9A — Analyse des risques

Échelle : probabilité (P) et impact (I) de 1 à 5; criticité = P × I.

| Risque | P | I | Criticité | Prévention / gate |
|---|---:|---:|---:|---|
| Suspension couplée à la révocation des rôles | 5 | 5 | 25 | R1 attribue l'effet et interdit le couplage implicite |
| Confusion statut / authentification / session | 4 | 5 | 20 | R1 définit les frontières et les faits |
| Rejeu classé sans identité complète action/contexte | 3 | 5 | 15 | R2 avant Orchestration |
| Double écriture agrégat historique / journal lifecycle | 4 | 5 | 20 | gate de coexistence avant Persistence |
| Owner Outbox `IdentityAccess` ambigu | 3 | 4 | 12 | audit owner avant migration Outbox |
| Exposition de données personnelles dans les événements | 3 | 5 | 15 | minimisation avant Event Contract |
| Consommateur aval reconstruisant le statut | 3 | 4 | 12 | responsabilité facts-only certifiée |
| Régression des fondations 4.8 | 2 | 5 | 10 | audit de diff et non-régression à chaque gate |
| Escalade d'autorité via endpoint futur | 3 | 5 | 15 | autorisation hors Workflow, audit avant HTTP |

## Risques résiduels acceptables en Discovery

La technologie de persistance, les noms de contrats et les payloads restent
indéfinis. Cette indétermination est volontaire : ils appartiennent aux
jalons ultérieurs et ne doivent pas être préemptés par le Blueprint.
