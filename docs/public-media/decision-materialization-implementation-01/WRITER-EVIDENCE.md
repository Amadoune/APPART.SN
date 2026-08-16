# Writer Evidence

Le writer PostgreSQL existant reste l'unique writer. Son verrou advisory et son verrou de ligne arbitrent les écritures monotones. Les résultats fermés conservés sont `Applied`, `AlreadyApplied`, `RejectedObsolete` et `Divergent`.

Le support V2 utilise la colonne JSONB existante ; aucune migration n'est nécessaire.
