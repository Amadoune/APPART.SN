# Classification de la défaillance

## Alternatives écartées

- donnée métier absente : non, les faits Listing/Property/Media sont présents ;
- binding absent : non, le binding et la requête PostgreSQL sont exécutables ;
- dépendance circulaire exécutée : non, aucun consumer ne tente actuellement un retour Projection → SearchDecision ;
- corruption : non, aucune ligne n'existe à mapper ;
- défaut de Projection Store : non atteint ;
- frontière historique incorrecte : non décidée ici, car elle est explicitement certifiée.

## Classification finale unique

**Source technique autoritative non matérialisée.**

La décision Search finale exigée par la source de Projection n'est produite par aucun chemin productif. Le `NotReady` est la réduction fail-closed correcte de cette absence.
