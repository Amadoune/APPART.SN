# Source Assembly Evidence

`PostgreSqlPublicSearchMaterializationSourceReaderV1` est une lecture d’infrastructure owner-scoped. Elle assemble :

- Listing : état courant et dernière révision Lifecycle ;
- Property : état canonique et révision issue du promotion ledger ;
- Media : collection unique, version et primaire actif.

Les résultats sont fermés : `Found`, `Missing`, `Corrupted`, `DependencyUnavailable`. La source ne consulte jamais Public Projection.

Pour RC2, les sources ont été trouvées et ont produit une projection Search `visible`.
