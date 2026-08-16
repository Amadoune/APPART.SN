# V1 Policy

Règle complète :

`candidature de projection Listing versionnée` → `SearchRank(0)` + `facets=[]` + `public-search-ranking-policy-v1`.

Cette politique suffit à matérialiser une décision déterministe, calculer un checksum, supporter le replay et avancer un watermark sur les révisions sources. Elle ne prétend pas différencier commercialement les annonces.

Seule une annonce Published satisfaisant `SearchVisibilityPolicy` peut devenir Visible. Les états Hidden ou Removed conservent la même sortie de ranking ; leur état de projection neutralise leur exposition.
