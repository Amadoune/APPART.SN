# Compatibility report

Le blueprint réutilise Domain events, Public Projection Outbox delivery, store JSONB et writer Public Geography. Il ne modifie aucune règle Listing, Property, Media, Search, ContentSeo ou Projection.

Rename nécessite une adaptation atomique de transport, pas un nouvel événement métier. Available/Unavailable est une évolution V2 déjà requise par le no-URL alignment et n'impose aucune migration.
