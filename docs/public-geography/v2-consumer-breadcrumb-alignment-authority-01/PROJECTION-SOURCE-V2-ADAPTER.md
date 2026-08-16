# Projection source V2 adapter

Adapter : V2 Available → locality + items `(placeId,type,officialName)` + positive watermark. V2 Unavailable → source not ready. Missing/Corrupted restent fail-closed.

L'adapter ne construit aucune CanonicalUrl et ne dépend pas de Blade, Search ou routes.
