# Title / Description Authority

Owner source : Listing Authoring.

Contrat de lecture candidat : reader owner-scoped du `ListingDraftState`, jamais Public Projection. Le draft persistant fournit `title`, `description`, `version`, `last_intent_id` et `last_intent_checksum`.

La publication lie l’authoring au Lifecycle via `AuthoringPublicFactHandoffV1` : `authoringVersion`, `sourceIntentId`, `sourceChecksum`, puis `publishedRevisionId` et `publishedAt`.

Le RC2 conserve titre et description au draft v1 et un handoff Published. Ces champs sont donc disponibles et révisionnables sans lecture UI.
