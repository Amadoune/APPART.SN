# SEO Listing Projection — Sprint 3.3B

## Rôle

`SeoListingProjection` est le modèle de lecture passif d'une future page SEO d'annonce. Il est `final readonly`, sans comportement métier, événements, framework ou persistance.

## Source et reconstruction

`SeoListingProjectionBuilder` accepte uniquement une `ListingSeoDecision` déjà acquise. Il copie ses valeurs dans la projection. Aucun Catalog, Aggregate, Repository ou service externe n'est consulté.

Une même décision produit toujours la même projection ou toujours `null`. Aucune valeur n'est générée pendant la reconstruction.

## États de sortie

- **Removed** : `pageTreatment=remove`; le builder retourne `null`.
- **Indexable** : page conservée, `indexable` et `index_follow` copiés depuis la décision.
- **Noindex** : page conservée, `not_indexable` et `noindex_follow` copiés depuis la décision.

Pour un Listing expiré, `ExpiredListingTreatment::Remove` supprime la projection et `RetainNoIndex` conserve une projection noindex. Le builder ne choisit jamais ce traitement.

## Dépendances

La projection ne dépend que des valeurs primitives et de `DateTimeImmutable`. Le builder dépend de `ListingSeoDecision` et de sa disposition de page ContentSeo. Il n'importe aucun Aggregate source.

## Exclusions

- aucune décision de canonical, robots ou indexabilité;
- aucune composition de breadcrumb ou structured data;
- aucun calcul d'URL ou slug;
- aucune persistance, Registry, Repository, SQL ou migration;
- aucune API, Vue, page HTML ou sitemap;
- aucun Dispatcher, Outbox, listener, queue ou Unit of Work.

## Limites

La projection reflète exactement la décision reçue. Elle ne garantit pas sa fraîcheur et ne réévalue aucune source. Toute évolution SEO doit produire une nouvelle `ListingSeoDecision`, puis reconstruire intégralement cette lecture.

Les futures pages publiques pourront consommer directement la projection sans accéder aux Aggregates ni reproduire une policy ContentSeo.
