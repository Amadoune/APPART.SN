# Successor Ancestry Audit

La source R12 actuelle est construite ainsi :

`R5 0067a774` → `F22 e0f76f6b` → `6ec7d1ec`.

La chaîne successor certifiée suit une branche sœur :

`R5 0067a774` → R6 → R7 → `R8 ebef23e1` → `R9 afa49464` → `R10 145cd1c6`.

Conséquence : F22 a correctement transporté ses corrections migration/PostgreSQL, mais il est parti directement de R5 et n'a jamais reçu les corrections Architecture R8. La présence des blobs R5 dans les sept fichiers de `6ec7d1ec` n'est pas une suppression ultérieure : ces blobs n'ont simplement jamais été remplacés sur cette branche.

La situation est analogue au défaut APP_URL : une autorité successor qualifiée existe dans R10, mais la branche corrective issue de R5 ne la contient pas.

Classification fermée : `ARCHITECTURE_SUCCESSOR_ANCESTRY_DEFECT`.
