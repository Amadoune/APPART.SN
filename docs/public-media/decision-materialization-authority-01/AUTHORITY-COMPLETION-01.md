# Authority Completion 01

## Clôture du NO GO historique

Le NO GO initial est conservé : `PublicMediaItem` exigeait une URL publique alors que Media ne fournissait qu'une storageKey privée. `PUBLIC MEDIA BINARY DELIVERY & URL AUTHORITY 01` ferme cette cause avec `/media/{mediaId}/revisions/{assetVersion}`.

Le matérialiseur connaît MediaId et assetVersion depuis les owners; il ne connaît ni hostname ni storageKey. V2 persiste le locator relatif et la frontière HTTP dérive l'URL absolue.

## Décision

L'autorité est complète. Empty media reste `SourceNotReady`; une décision exige un primary public éligible. Le writer existant demeure l'unique store authority.

## Verdict

GO proposé. Deux implementations séparées et ordonnées sont autorisées : Binary Delivery, puis Public Media Decision Materialization.
