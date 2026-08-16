# Final Decision Identity

`decisionId` est un UUIDv5 déterministe conforme à la convention d'identité certifiée existante :

- namespace UUID URL : `6ba7b811-9dad-11d1-80b4-00c04fd430c8` ;
- name UTF-8 exacte : `https://appart.sn/search-discovery/public-search-decisions/public-search-ranking-policy-v1/{listingId}` où `{listingId}` est l'UUID canonique lowercase.

L'identité est stable pour tous les replays et toutes les révisions sources sous policy v1. Elle ne reprend ni commandId, ni eventId, ni horloge, ni random.

Une future policy change la composante finale du name et produit une nouvelle identité déterministe ; son application impose aussi une nouvelle version SearchDecision.
