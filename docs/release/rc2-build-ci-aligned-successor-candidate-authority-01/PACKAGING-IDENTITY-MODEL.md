# Packaging Identity Model

Le script de packaging vérifie minimalement :

- le tag attendu exact et annoté ;
- sa résolution exacte vers `HEAD` ;
- l'ascendance du commit RC2 prédécesseur ;
- la propreté Git ;
- les checksums des lockfiles.

Le futur script doit attendre `appart-sn-release-candidate-rc2-r2` et la base `ab5d3f57a577160d3aae36cee5778dc7bae59a16`. Aucun SHA/tree successor ne doit être embarqué.
