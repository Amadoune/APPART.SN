# Phase 4.7C-R1 — Transition Execution Context Analysis

## Décision

Le contexte d'exécution V1 transporte toutes les données explicites nécessaires à la future persistance contextuelle et à la mutation miroir certifiée 4.7B-R2 :

* version Lifecycle attendue ;
* acteur ;
* instant UTC ;
* identités de décision propres à l'action ;
* motif historique typé ;
* `Decision Context V1` certifié, sans reconstruction.

Le motif historique est nécessaire au miroir de compatibilité ; il reste distinct de la preuve fermée de présence portée par le Decision Context.

## Frontière

Le contexte ne décide aucune transition. Le Workflow reste l'unique propriétaire du chemin nominal. Le rejeu utilise exclusivement le dernier snapshot exact et ne rappelle jamais le Workflow.
