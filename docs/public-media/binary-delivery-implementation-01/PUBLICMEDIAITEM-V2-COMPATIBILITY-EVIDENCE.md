# PublicMediaItemV2 Compatibility Evidence

Le locator V2 est déterministe :

`mediaId = X`, `assetVersion = 2` → `/media/X/revisions/2`.

La route implémentée consomme exactement ces deux identités et résout exactement le même asset. Aucun slug, filename, hostname ou paramètre signé n'est ajouté ou persisté.
