# Projection Source Evidence

Après le catch-up RC2, `SearchDecisionReader` retourne :

- status : `found` ;
- decisionId : `20d5ab5a-45ac-5a5e-9f15-d1357e9105a9` ;
- version : `1` ;
- state : `visible` ;
- rank : `0` ;
- facets : `0`.

L’inspection read-only de `CertifiedPublicListingProjectionSource` retourne `content_seo_missing`. Le blocage antérieur `search_missing` a donc disparu. Ce contrôle n’a exécuté aucune écriture ni activation Projection.
