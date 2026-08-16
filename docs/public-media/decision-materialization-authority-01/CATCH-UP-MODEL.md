# Catch-up model

Le catch-up productif prend un ListingId, vérifie Published, résout Property/MediaCollection, assemble depuis les owners, puis appelle le writer normal. Replay identique doit être AlreadyApplied.

Le modèle est conceptuellement fermé mais inexécutable tant que la source URL publique n'est pas définie.
