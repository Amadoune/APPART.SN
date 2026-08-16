# Source Revision Evidence

Le vecteur canonique contient Listing `{id, publicationVersion, state}`, collection `{id, version}`, attachments `{mediaId, aggregateVersion, intentChecksum}` et assets `{mediaId, assetVersion, state, contentChecksum}`.

Son SHA-256 est persisté dans le payload V2. Les clés sont normalisées avant checksum. Égal donne replay, dominant avance la version, dominé est obsolete et incomparable est divergent.
