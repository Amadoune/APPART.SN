# Public Projection PostgreSQL — analyse

## Contrat implémenté

Le Store PostgreSQL implémente sans modification `PublicListingProjectionWriter` et `PublicListingQuery`. Il persiste générations, états Current/Historical/Tombstone, read model final et watermark vectoriel. Les décisions de canonical et de contenu restent en amont ; l'adaptateur interprète uniquement les relations et résultats fermés du contrat.

## Modèle physique

Le schéma propriétaire `public_projection` contient `generations` et `listing_projections`. Une génération active unique est imposée par index partiel. La clé `(generation_id, canonical_path)` réserve durablement un canonical, y compris Historical. Un index partiel garantit un seul record non historique par listing et génération.

Les versions sont contraintes positives selon le contrat. Un read model est présent si et seulement si l'état est Current. Les recherches publiques joignent exclusivement la génération Active et l'état Current par canonical exact.

## Concurrence et transactions

Chaque écriture verrouille la génération concernée avant de lire et appliquer l'état. Ce verrou sérialise les décisions concurrentes dans une génération sans verrou global inter-générations. Le Writer rejoint une transaction externe existante ou possède sa transaction locale. Une erreur rollback l'opération complète ; le remplacement canonical écrit Historical et Current atomiquement.
