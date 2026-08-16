# Compatibility Report

- `SearchRank` reste compatible avec sa plage 0..10000.
- `SearchProjectionPolicy` peut recevoir la baseline sans modification conceptuelle.
- `SearchFacetPolicy` accepte la liste vide et reste disponible pour une future version.
- ListingLifecycle, Property, Media, Geography, Professionals et MonetizationPayments conservent leurs ownerships.
- PublicationReview, Projection et ContentSeo ne reçoivent aucun pouvoir de ranking.
- Les valeurs historiques 100/500/600 restent non normatives.
- Le Listing RC2 Published est préservé et aucune donnée n'est écrite.
- Search UX/API reste fermée.

Aucune migration, régression contractuelle ou changement produit runtime n'est introduit.
