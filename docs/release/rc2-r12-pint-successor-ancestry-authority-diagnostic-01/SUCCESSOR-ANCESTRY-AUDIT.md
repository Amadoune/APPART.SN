# Successor Ancestry Audit

Branche actuelle : `R5 0067a774` → `F22 e0f76f6b` → `6ec7d1ec`.

Branche successor : `R5 0067a774` → R6 → R7 → R8 → `R9 afa49464` → R10.

Les trois fichiers restent sur leur blob R5 jusqu'à R8 inclus. R9 introduit simultanément les trois blobs conformes Pint; R10 les conserve sans delta. F22 et `6ec7d1ec` appartiennent à une branche sœur créée avant cette intégration.

Cette topologie est identique au mécanisme des précédents défauts d'ascendance : une correction successor existe mais n'est pas ancêtre de la branche corrective issue de R5.

Classification fermée : `PINT_SUCCESSOR_ANCESTRY_DEFECT`.
