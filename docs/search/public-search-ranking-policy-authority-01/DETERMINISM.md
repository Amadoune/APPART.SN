# Determinism

Invariant : mêmes faits sources versionnés et même version de policy donnent le même `SearchRank` et les mêmes facettes.

V1 retourne toujours `SearchRank(0)` et `[]` pour son entrée Published admissible.

Random, wall clock, ordre d'arrivée, trafic, état du Projection Store, données UI et valeurs de fixtures sont interdits.
