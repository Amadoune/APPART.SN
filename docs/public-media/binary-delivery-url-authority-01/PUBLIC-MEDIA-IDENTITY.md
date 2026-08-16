# Public Media identity

L'identité publique stable est `MediaId`. Dans l'implémentation actuelle, `MediaId` et `AssetId` sont la même identité lors de `AttachReadyMediaAssetV1`, mais le delivery ne doit pas exposer ownerId ni storageKey.

La révision de locator est `assetVersion`. Le tuple public est donc `(MediaId, assetVersion)`.
