# Corroboration PostgreSQL

Les lectures sont réalisées par les stores ou outils de validation certifiés, sans écriture SQL de démonstration.

Après Submit : Property promue, promotion ledger, Listing Aggregate Submitted, Workflow Submitted et Queue présente.

Après Claim : Queue assignée au reviewer attendu et version avancée.

Après BeginReview : Aggregate et Workflow `UnderReview` avec versions cohérentes.

Après Approve : Aggregate et Workflow `Published`, Queue `Completed`.

Après Projection : read model du Projection Store et activation ledger uniques.

Chaque checkpoint consigne les IDs, versions et identités de commande correspondants. Toute discordance avec HTTP/UI déclenche l'arrêt fail-fast.
