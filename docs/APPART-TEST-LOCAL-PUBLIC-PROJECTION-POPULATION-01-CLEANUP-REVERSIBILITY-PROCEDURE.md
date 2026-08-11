# APPART.TEST LOCAL PUBLIC PROJECTION POPULATION 01 — Cleanup / Reversibility Procedure

## État courant

Aucune donnée n'a été créée pendant le jalon ; aucun nettoyage n'est donc requis.

## Exigence pour une future population autorisée

La réversibilité devra être portée par une procédure applicative locale symétrique qui :

1. identifie exclusivement les données par un marqueur de développement réservé ;
2. retire ou archive la publication par les transitions certifiées ;
3. propage cette décision dans le pipeline public ;
4. retire la projection par les writers certifiés ;
5. ne supprime jamais directement une ligne de projection ou de génération ;
6. vérifie qu'aucune donnée non locale n'est affectée.

Une suppression SQL, un reset global de base ou un rollback de migration ne constitue pas une procédure acceptable pour ce besoin produit local.
