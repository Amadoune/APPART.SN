# Final PublicMediaItemV2

```text
PublicMediaItemV2 {
  mediaId: UUID
  publicLocator: relative canonical path
  deliveryRevision: positive assetVersion
  order: positive integer
  primary: boolean
}
```

Aucune storageKey, URL absolue, hostname, originalName ou variante. L'original ready suffit. Une collection publique contient exactement un primary et des ordres uniques et contigus.

V1 reste lisible. V2 est discriminée par `schemaVersion: 2`; schema inconnu donne `Corrupted`. L'adapter V1 dérive cover/gallery et URL absolue depuis V2.
