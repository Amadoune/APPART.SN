# SearchRank — sémantique

## Invariants démontrés

- type : entier ;
- minimum : 0 ;
- maximum : 10000 ;
- construction rejetée hors bornes ;
- égalité par valeur.

## Sémantique non définie

- ordre ascendant ou descendant ;
- « meilleur » score ;
- rang neutre ;
- baseline Published ;
- pondérations ;
- unité ;
- relation avec fraîcheur, qualité ou promotion.

Les consumers actuels conservent le rang dans `SearchProjection`, le sérialisent dans `SearchDecision` et l'incluent dans le checksum. Aucun reader public ou requête productive auditée ne trie dessus.

Conclusion : `SearchRank` est un contenant validé, pas une policy de ranking.
