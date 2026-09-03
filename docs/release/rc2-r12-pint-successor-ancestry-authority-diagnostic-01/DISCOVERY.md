# RC2 R12 Pint Successor Ancestry Authority — Discovery

Le gate Pint complet échoue exclusivement sur `ordered_imports` dans trois fichiers identiques aux blobs R5. Le jalon F22 consignait déjà exactement ces trois fichiers comme baseline Pint R5 en échec.

Git démontre que les trois corrections ont été introduites ensemble par `afa494648d082a6f85825a1ad05b80d712befc8f`, commit de matérialisation RC2 R9 Pint, puis conservées byte-for-byte dans R10. La branche R5→F22→`6ec7d1ec` a divergé avant R9 et n'a jamais intégré ces blobs.

Classification : `PINT_SUCCESSOR_ANCESTRY_DEFECT`. Ce n'est ni une régression R12, ni une dérive de version/configuration de Pint.
