# Public Projection Rebuild et générations

## Protocole

- Créer une génération Candidate de façon idempotente.
- Exécuter `runOnce(generation, scope, checkpoint)` jusqu’à un checkpoint nul.
- Construire un manifeste attendu à partir des sources autorisées.
- Valider la Candidate ; toute absence, divergence de watermark ou corruption de checksum bloque la suite.
- Activer atomiquement la Candidate avec la preuve de validation.
- Conserver la génération précédente en état Retired pour un rollback ciblé.

## Rebuilds autorisés

- Full : toutes les identités fournies par l’énumérateur, page par page.
- Listings : ensemble explicite et non vide.
- Range : bornes ordonnées et explicites.

L’énumérateur est un port. Le sprint ne fournit aucun scan global ni source Runtime. L’orchestrateur ne déduit aucune identité et ne dépasse jamais la taille de page configurée.

## Échecs observables

Les éléments absents sont comptés séparément. Les records incohérents ou refusés par le Writer sont listés comme rejets. La validation expose les identités manquantes, divergentes et corrompues. Aucun de ces cas ne déclenche une correction automatique.

## Limites

Le rebuild ne modifie pas la génération Active. La visibilité publique change uniquement lors de la transaction d’activation. Le rollback change uniquement les états des générations ; les records et les canonicales historiques restent intacts.
