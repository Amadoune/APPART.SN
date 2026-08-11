# APPART.TEST LISTING PUBLICATION REGISTRY SYNCHRONIZATION 01 — PostgreSQL Evidence

## Architecture transactionnelle observée

`PostgreSqlListingPublicationWorkflowRepository` protège chaque Listing par advisory lock transactionnel, applique l'optimistic locking et respecte une transaction externe existante.

`ListingRegistry::save` porte séparément l'optimistic locking de l'Aggregate. Les deux repositories sont composés avec la même connexion PDO dans le provider actuel, rendant une transaction locale techniquement envisageable.

## Limite

Aucun test PostgreSQL de synchronisation n'a été créé ou revendiqué : il manquerait nécessairement un contrat autoritatif fournissant les données Aggregate. Les preuves PostgreSQL existantes couvrent le workflow et son Outbox, pas la convergence workflow/Registry.

## Intégrité

Aucune donnée PostgreSQL, migration, snapshot ou table de projection n'a été modifié pendant ce jalon.
