# Test Matrix

## Unit

- UUID canonical → path attendu ;
- déterminisme et replay ;
- UUID invalide/uppercase rejeté ;
- changements title/Geography/PropertyType sans effet ;
- collision réduite fail-closed.

## Architecture

- owner ContentSeo ;
- aucune Projection, Search UX/API, horloge, random ou slugger.

## Intégration

- Listing Published → path UUID → `CanonicalPolicy` acceptée → route compatible ;
- canonical absolue à host apex ;
- Listing non Published refusé.
