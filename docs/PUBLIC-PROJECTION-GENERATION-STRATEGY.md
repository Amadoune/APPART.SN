# Stratégie PostgreSQL des générations publiques

Le schéma 3.6D reste inchangé. La contrainte partielle `public_projection_one_active_generation` constitue la dernière défense garantissant une seule génération Active.

Les transitions autorisées par 3.6E sont :

- création idempotente vers Candidate ;
- Candidate vers Active, uniquement avec validation complète ;
- Active précédente vers Retired dans la même transaction ;
- Retired ciblée vers Active pour le rollback, avec retrait atomique de l’Active courante.

Les verrous `FOR UPDATE` sont acquis sur les générations dans l’ordre de leur identifiant. Les bascules concurrentes sont donc sérialisées sans toucher aux lignes de projections. Une transaction externe reste propriétaire du commit ou du rollback.

La validation lit les records Candidate sans mutation. Le Mapper 3.6D vérifie le checksum du payload ; le validateur compare ensuite chaque watermark vectoriel au manifeste attendu. Une Candidate non vide mais incomplète n’est pas considérée valide.
