# Public Projection Reconciliation and Replay

## Architecture

La fondation sépare quatre responsabilités : `PublicProjectionReconciliationSource` fournit une page bornée, le Detector produit des divergences typées, la Policy analyse et explique, puis `PublicProjectionReplayPlanner` enregistre uniquement les demandes ciblées.

`runOnce(checkpoint)` traite au plus la taille configurée et retourne le prochain checkpoint. Une interruption reprend à ce checkpoint ; aucun scan global, daemon ou boucle permanente n'existe.

## Détection et décisions

| Divergence | Décision |
|---|---|
| message durable manquant | replay Message ou Range |
| trou causal | replay Range |
| high-watermarks différents | replay HighWatermark |
| blocage SourceReadiness | Wait, aucune réparation silencieuse |
| blocage SequenceGap durable | replay Aggregate |

Chaque analyse contient une explication non vide. Les décisions sont exhaustives sans branche par défaut.

## Replay et invariants

Les demandes réutilisent les scopes certifiés Message, Aggregate, Module, Range et HighWatermark ainsi qu'une autorisation explicite. Le moteur ne redélivre rien directement, ne crée aucune nouvelle causalité et ne reconstruit aucune projection.

At-least-once, idempotence, absence de double effet et absence de perte restent la responsabilité du pipeline déjà certifié. La réconciliation rend les écarts observables et prépare seulement leur reprise ciblée.

## Limites et Runtime futur

Le Runtime devra fournir une source bornée capable de lire les observations autorisées et un planner durable. Ce sprint ne crée aucun SQL, PostgreSQL, Laravel, HTTP, binding, Job, Queue, scheduler, Repository ou composant d'exploitation.
