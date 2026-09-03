# Authority Assessment

## Preuve historique

- Le commit/tag R8 est explicitement matérialisé comme correction Architecture.
- Les sept blobs correctifs apparaissent ensemble à R8.
- Ils restent byte-for-byte identiques dans R9 puis R10.
- R9 et R10 ont été matérialisés comme successors sans revert de ces corrections.
- La baseline F22 consigne exactement le résultat courant : 970/987 PASS, 15 FAIL et 2 ERROR.

Le dépôt ne contient pas un transcript autonome donnant le compteur d'une exécution Architecture complète sur R8/R9/R10. La preuve disponible est l'identité immuable du commit/tag de correction et la conservation exacte des blobs dans les successors certifiés. Cette limite probatoire devra être compensée par une exécution Architecture complète après toute restauration autorisée.

## Qualification actuelle

La qualification courante reste FAIL sur les blobs R5. Aucun test n'a été exécuté pendant ce diagnostic et aucun PASS R8/R9/R10 n'est réutilisé comme PASS R12.

## Correction proposée

Restaurer exactement les sept blobs R8 identifiés, puis reprendre le gate Architecture complet depuis l'état R12 autorisé. Cette proposition n'est pas une correction exécutée.
