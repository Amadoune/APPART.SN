# Migration Decision

**Aucune migration.**

`content_seo.public_source_snapshots` porte déjà ListingId, snapshotId, version, JSONB, checksum et métadonnée technique. Reader, mapper et writer sont compatibles avec le blueprint.

Si l’autorité canonical exige un registre d’unicité ou un historique non représentable, son propre chantier devra stopper et qualifier la migration avant toute implémentation ContentSeo.
