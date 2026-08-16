# Snapshot Origin

Le repository contient un reader, un writer et un mapper complets, mais aucun use case productif créant `ContentSeoSourceDecision`.

Les seules constructions hors tests sont dans `CreateLocalFirstListing` et `CreateLocalPublicFactListing`. Elles fixent manuellement contenus, canonical, UUID, révisions et temps : ce sont des commandes de démonstration, non une autorité réutilisable.

Les anciens use cases `GenerateSeoProjection` travaillent avec `SeoProjectionRegistry` et des Catalogs, pas avec le snapshot exigé par Public Projection ; leurs adapters ContentSeo productifs ne sont pas composés.
