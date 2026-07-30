# Phase 4.7A-R1 — Administrative Action Decision Context Analysis

## Problème contractuel

`Record` ne possède pas une cible unique : il mène à `Recorded` ou `PendingApproval`. Cette branche dépend d'une décision propriétaire four-eyes. De plus, l'enregistrement exige un motif, tandis qu'une approbation ou un rejet exige un acteur indépendant.

Le futur Workflow ne doit ni lire l'Aggregate historique, ni appeler `FourEyesPolicy`, ni recevoir le texte du motif. Le contexte V1 matérialise donc seulement les preuves et décisions nécessaires.

## Propriété

`AdministrationAudit` reste l'unique propriétaire :

- de la preuve de présence du motif ;
- de la décision d'enregistrement direct ou d'approbation indépendante ;
- de l'identité de l'auteur ;
- de l'identité de l'acteur de décision ;
- de la validité de leur relation.

## Séparation technique

Un contexte `Available` porte exclusivement les preuves métier. `Unavailable` porte exclusivement `SourceUnavailable` ou `CorruptedEvidence`. Une indisponibilité ne peut jamais être convertie en `Missing`, en enregistrement direct ou en refus métier.
