# Content/SEO Source Snapshot Reader

Le reader retourne `Found`, `Missing` ou `Corrupted`. `Found` contient le snapshot exact. Toute erreur JSON, Value Object invalide ou divergence de checksum produit `Corrupted` sans fallback.

Le snapshot contient : identité, Listing, version, `ListingSeoSource`, `SearchSeoSource`, `PropertySeoSource`, historique canonical et `decisionAt`. Ces valeurs sont déjà décidées par ContentSeo ; le store ne les transforme pas.

Le writer publie les snapshots via `Applied`, `AlreadyApplied`, `RejectedObsolete` ou `Divergent` et rejoint une transaction externe lorsqu’elle existe.
