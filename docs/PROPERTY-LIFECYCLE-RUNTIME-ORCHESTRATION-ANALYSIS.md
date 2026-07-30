# Property Lifecycle Runtime Orchestration Analysis

## Responsabilité

`DeterministicPropertyLifecycleOrchestrator` coordonne exclusivement `PropertyLifecycleWorkflow` et `PropertyLifecycleWorkflowStore`. Il ne possède aucune matrice, ne construit aucune transition et ne transforme aucune exception technique en décision métier.

## Séquence

L'état courant est lu, sa version est comparée à `expectedVersion`, puis le workflow reçoit exactement l'état et l'action. Un refus est retourné avec le diagnostic original et sans écriture. Une autorisation transmet exactement la transition au store avec la version suivante.

Les anomalies de lecture et d'écriture restent des résultats d'orchestration techniques. Aucun événement, HTTP, Outbox ou Aggregate n'est impliqué.

## Concurrence et déterminisme

Le contrôle préalable de version protège contre une commande obsolète. Le store conserve son contrôle atomique lors de l'append. À requête, lecture et résultat de persistance identiques, le résultat d'orchestration est identique. Il n'existe aucune horloge ou identité générée.
