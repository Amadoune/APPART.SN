# P09 — Implementation Evidence

## Surfaces

- Layout SEO partagé : `resources/views/layouts/public.blade.php`.
- Home : `resources/views/home.blade.php`.
- Recherche : `resources/views/public-search-results.blade.php`.
- Fiche : `resources/views/public-listing.blade.php`.
- Sitemap read-only : `app/Http/Controllers/PublicSitemapController.php` et `resources/views/sitemap.blade.php`.
- Robots : `public/robots.txt`.
- Route publique : `GET /sitemap.xml`.

## Frontière

Le sitemap dépend uniquement de `PublicSearchResultsReaderV1` et `PublicListingQuery`. Il parcourt la pagination publique, résout les fiches, exclut tout résultat non indexable et reprend les canonical et dates projetés. Il n'accède ni à PostgreSQL, ni à un repository, ni à IAM, Property, Media ou Listing Lifecycle.

## Preuve visuelle

`P09-PUBLIC-DISCOVERABILITY.png` représente la recherche locale réelle. Aucun résultat fictif n'a été ajouté pour masquer l'état vide.
