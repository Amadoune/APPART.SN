# Sprint 3.6G — HTTP Runtime Certification Analysis

Le chemin certifié est `HTTP -> Laravel -> PublicListingController -> PublicListingQuery ->
PostgreSqlPublicListingProjectionStore`. Le contrôleur injecte uniquement le Query public et transmet
le ReadModel durable à la vue.

Le Store filtre la génération Active et l'état Current. Une canonical Historical, une Tombstone, une
Candidate et un ListingId ne peuvent donc pas devenir une page publique. Aucun redirect historique
n'est ajouté avant son sprint dédié.

Une absence rend 404. Toute exception du Query, notamment indisponibilité PostgreSQL ou checksum
divergent, est convertie en 503. Le contrôleur ne tente aucun fallback et ne relit aucune source.
