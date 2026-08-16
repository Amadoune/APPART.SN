# Failure Classification

Catégorie retenue : **autorité normative et pipeline/handoff de matérialisation manquants**.

Alternatives écartées :

- binding absent : faux, binding productif présent ;
- store non déployé : faux, table présente et requête réussie ;
- source technique corrompue : faux, aucune ligne ;
- dépendance circulaire : non démontrée ;
- source métier entièrement absente : faux, la majorité des faits owner existent ;
- simple catch-up oublié : faux, aucun matérialiseur générique certifié n’existe.
