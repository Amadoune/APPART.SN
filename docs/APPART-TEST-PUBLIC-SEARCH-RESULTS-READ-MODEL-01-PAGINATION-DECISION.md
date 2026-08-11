# APPART.TEST Public Search Results Read Model 01 — Pagination Decision

Décision : `SUPPORTED`.

- ordre déterministe : `canonical_path ASC` ;
- pagination par curseur exclusif `after` ;
- taille par défaut : 12 ;
- borne maximale : 24 ;
- lecture de `limit + 1` pour déterminer `nextCursor` ;
- aucun `OFFSET` ;
- aucun total count revendiqué.

Le curseur reprend exactement le dernier chemin canonique exposé. Une page terminale retourne `nextCursor: null`.
