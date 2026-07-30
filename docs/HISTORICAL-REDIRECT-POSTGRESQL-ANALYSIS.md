# Historical Redirect PostgreSQL Analysis

## But

La tranche 3.10B matérialise uniquement des décisions déjà produites par `ContentSeo`. Le resolver PostgreSQL ne calcule pas de canonical, ne consulte pas `PublicListingQuery`, ne suit pas de chaîne et ne choisit pas entre plusieurs décisions.

## Modèle retenu

Une ligne représente une décision identifiée par UUID : source historique, destination publique optionnelle, qualification `current` ou `historical`, révision, checksum et état d'intégrité. Plusieurs lignes peuvent partager une source afin que l'ambiguïté reste observable.

Une ligne `intact` est contrainte : révision positive, URLs `https://appart.sn` normalisées et forme destination/qualification atomique. Une ligne `corrupted` permet de conserver une anomalie historique explicitement signalée, mais son checksum conserve toujours un format SHA-256.

## Lecture

La recherche exacte utilise l'index `(historical_canonical, decision_id)`, un ordre stable et `LIMIT 2`. Zéro ligne donne `NotFound`; une ligne est validée et classifiée; deux lignes donnent `Ambiguous` après validation. Une corruption a priorité sur toute autre classification et ne produit jamais de cible.

## Frontières

Aucun ORM, Laravel, HTTP, Runtime, horloge, Aggregate, Repository métier ou identifiant Listing n'est impliqué.
