# Reprise 3.7E — Source Lookup and Readiness

Le Lookup concret est un classificateur sans effet. Listing, Search et Content/SEO sont inspectés
par `CertifiedPublicListingProjectionSource`. Property produit directement une requête multi-cibles
normalisée. Media charge sa collection par son identité officielle et reprend le `propertyId` porté
par cette collection ; aucune convention d'identifiant n'est utilisée.

Le Lookup n'appelle ni Updater, ni stratégie de pagination. Le Consumer 3.9C reste propriétaire du
parcours et de l'exécution.

La relation MediaCollectionId -> PropertyId est fonctionnelle et non ambiguë car l'identité de la
collection est une clé primaire. Une collection absente produit `MediaOwnershipMissing`; une
identité invalide ou un contenu non mappable produit un diagnostic permanent explicite.
