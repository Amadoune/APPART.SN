# Packaging Alignment

Le script de packaging attend désormais :

- base prédécesseur RC2-R2 exacte ;
- tag futur RC2-R3 exact.

Packaging continue de consommer `public/build` produit par l'étape frontend précédente. Il ne reconstruit pas le frontend et aucun double build n'est introduit.

Syntaxe Bash et preflight d'alignement : PASS. Archive produite : aucune.
