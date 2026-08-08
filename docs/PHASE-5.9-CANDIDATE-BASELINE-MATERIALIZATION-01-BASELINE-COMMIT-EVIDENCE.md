# Baseline Commit Evidence

La baseline doit être matérialisée par un commit unique portant le message :

`chore(release): materialize phase 5.9 candidate baseline`

Le SHA candidat est défini sans ambiguïté comme la valeur de :

`git rev-parse phase-5.9-baseline-candidate^{commit}`

Cette résolution par le tag annoté évite toute référence auto-référentielle impossible à inscrire dans l'arbre du commit lui-même. Le SHA terminal réellement obtenu est consigné dans le rapport d'exécution du jalon.
