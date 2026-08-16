# Final source revision model

Vecteur canonique checksumé :

```text
listing: {listingId, publicationVersion, state}
collection: {mediaCollectionId, collectionVersion}
attachment: {aggregateVersion, intentChecksum}
assets: [{mediaId, assetVersion, state, contentChecksum}]
```

Les assets suivent Media order puis MediaId. Le vecteur capture sélection, ordre/primary via collectionVersion, attachment, assetVersion/readiness et dépublication. Son SHA-256 est persisté dans V2.

Une source relue différente avant write invalide l'assemblage. Vecteur dominé : stale; égal avec payload différent : divergent; vecteurs concurrents incomparables : relecture/retry.
