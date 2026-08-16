# Final catch-up model

Le catch-up d'un Listing Published doit appeler le même `MaterializePublicGeographyDecisionV2` avec ListingId. Il ne fournit ni payload, vector, URL, version ou identity.

Le parcours RC2 ponctuel est techniquement assemblable, mais le modèle productif n'est pas certifiable tant que le refresh descendant ne garantit pas sa maintenance après mutation Geography.
