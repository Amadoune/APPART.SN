# Materialization Sequencing

Séquence normative :

1. RC2-R2 prédécesseur immuable ;
2. Build/CI Frontend Prerequisite Correction ;
3. RC2-R3 Successor Immutable Materialization ;
4. RC2 Reproducible Build reopening depuis zéro ;
5. External CI séparée ;
6. Production Readiness indépendante.

La correction et la matérialisation restent deux gates. Aucun tag n'est créé par le gate de correction.
