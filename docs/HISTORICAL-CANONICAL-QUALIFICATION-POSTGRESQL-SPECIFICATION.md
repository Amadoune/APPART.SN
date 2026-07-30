# Historical Canonical Qualification PostgreSQL Specification

`PostgreSqlHistoricalCanonicalQualifier` implémente directement `HistoricalCanonicalQualifier`. Il exécute une unique lecture exacte, ordonnée et bornée, puis délègue toute validation à `HistoricalCanonicalQualificationMapper`.

Le checksum SHA-256 couvre, séparés par LF : identifiant de décision, canonical, qualification et révision. Aucune horloge ni donnée Runtime ne participe au résultat.

Ordre de classification : absence, validation d'intégrité, cardinalité ambiguë, Current, Historical. Une corruption précède donc toujours un résultat exploitable. Aucune branche `default`, heuristique ou choix de ligne n'est admis.
