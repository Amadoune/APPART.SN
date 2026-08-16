# Read model evidence

`PublicListingReadModel` reprend les deux champs V2 sans migration car le store persiste le read model sérialisé. Le builder V2 déduit la ville depuis l’item de type `city`; le comportement positionnel historique V1 reste inchangé.

Le canonical, canonicalHistory, JSON-LD, Search projection et les autres champs publics ne changent pas.
