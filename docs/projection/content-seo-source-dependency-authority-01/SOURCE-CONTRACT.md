# Source Contract

Contrat exact : `ContentSeoSourceSnapshotReader`, couche ContentSeo Application.

Méthode : `readByListing(ListingId): ContentSeoSnapshotReadResult`.

Statuts fermés : `Found`, `Missing`, `Corrupted`. Le contrat n’expose pas `DependencyUnavailable`; l’adapter SQL laisse remonter une indisponibilité PDO, tandis que les incohérences de mapping/checksum deviennent `Corrupted`.

Le snapshot contient identité, ListingId, version, sources Listing/Search/Property, historique canonical et `decisionAt`.
