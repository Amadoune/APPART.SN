# Writer Semantics

Contrat : `ContentSeoSourceSnapshotWriter::store(ContentSeoSourceDecision)`.

Implementation : `PostgreSqlContentSeoSourceSnapshotWriter`.

Le writer utilise une transaction locale, un advisory lock par ListingId, `SELECT ... FOR UPDATE`, checksum SHA-256 et upsert monotone. Résultats fermés : `Applied`, `AlreadyApplied`, `RejectedObsolete`, `Divergent`.

L’écriture existe mais n’est appelée par aucun pipeline productif générique.
