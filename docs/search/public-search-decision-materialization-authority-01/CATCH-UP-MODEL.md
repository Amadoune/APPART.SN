# Catch-up Model

Le catch-up doit utiliser exactement le futur matérialiseur normal : entrée ListingId Published, relecture owner-scoped des trois sources, même décision canonique, même writer et mêmes règles de version.

Pour le Listing RC2, aucune donnée ne doit être recréée. Après matérialisation légitime, la même intention Projection pourra être rejouée puisque son commandId n'a pas été enregistré lors de `NotReady`.

Le catch-up est actuellement **bloqué** : utiliser 500/600, fabriquer une facette, choisir une version 1 ou un UUID déterministe sans autorité violerait la mission. SQL direct, fixtures et commandes locales sont interdits.
