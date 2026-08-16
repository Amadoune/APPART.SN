# Migration Decision

NO MIGRATION. Table, index, FK et transitions existent. L'identité initiale est une donnée d'exploitation créée via Manager, pas une donnée de migration. Aucun INSERT de migration, seeder ou fixture ne peut remplacer l'opération certifiée.
