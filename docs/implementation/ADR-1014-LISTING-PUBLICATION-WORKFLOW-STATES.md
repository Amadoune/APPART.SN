# ADR-1014 — Listing Publication Workflow States

## Statut

Accepté pour le Sprint 4.1A.

## Décision

Le workflow reprend exactement les dix états du Listing Domain certifié et ses vingt-sept transitions. Il ne crée ni `Approved`, ni `Deleted`, ni `PendingReview` générique.

`Submitted` et `UnderReview` séparent attente et instruction. L'approbation est atomique avec Published. Archived est terminal et conserve l'audit; aucune suppression métier destructive n'est modélisée.

## Conséquences

Les futures couches de persistance et d'orchestration disposent d'une décision structurelle unique, cohérente avec l'Aggregate existant. Les preuves, acteurs, déclencheurs, dates et disponibilités restent contrôlés par les politiques Domain et ne sont pas dupliqués dans ce workflow.

## Alternatives écartées

Une taxonomie simplifiée aurait perdu les corrections, retraits et routes de renouvellement. Ajouter Approved ou Deleted aurait créé une divergence avec le Domain certifié.
