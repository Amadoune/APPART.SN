# Lead Eligibility PostgreSQL Storage Strategy

Le stockage est un journal append-only unique. Sa clé `(listing_id, version)` garantit une ligne par révision Listing. Le verrou advisory transactionnel dérivé du `ListingId` sérialise les writers concurrents.

La lecture courante utilise `ORDER BY version DESC LIMIT 1` et l'index `lead_eligibility_current_lookup`. L'historique utilise le même journal en ordre croissant.

Une table snapshot séparée est écartée : elle dupliquerait l'état courant sans ajouter de garantie et imposerait une synchronisation supplémentaire.
