# Stratégie PostgreSQL Public Geography

`public_geography.decisions` stocke une ligne par place. La ligne porte contenu et révision ensemble. Le verrou transactionnel est ciblé par `placeId`; le writer rejoint les transactions externes. `updated_at` est opérationnel et ne participe jamais à la version.

La migration est idempotente. Le rollback structurel supprime uniquement cette table et son schéma s’il est vide.
