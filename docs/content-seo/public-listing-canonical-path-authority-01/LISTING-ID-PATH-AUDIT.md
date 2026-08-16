# Listing ID Path Audit

`annonces/{listingId}` est retenu car il est :

- stable pendant toute la vie du Listing ;
- unique par construction ;
- déterministe et sans horloge/random ;
- accepté par la route et `CanonicalPolicy` ;
- indépendant des changements de contenu ;
- compatible avec les stores et le mécanisme Historical Redirect.

Le choix privilégie l’intégrité et la reproductibilité. Aucun objectif produit certifié n’exige un slug descriptif pour les nouveaux Listings.
