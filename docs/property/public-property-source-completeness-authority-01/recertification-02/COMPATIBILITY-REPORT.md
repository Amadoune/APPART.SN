# Compatibility Report

La recertification ne modifie aucune capacité produit.

Sont préservés : F0 persistence et lifecycle Place, F1 sélection, F2 Address Identity, F3 Business Year, F4-A/F4 Authoring, F5-A Catalog, RegisterProperty, ChangeAddress, PropertyTypePolicy, Projection et Search.

La seule nouvelle pièce de ce chantier est un test de certification PostgreSQL qui assemble les sources et s’arrête avant la Promotion. Il ne persiste aucun Aggregate Property et n’introduit aucun runtime, migration, Provider ou binding.

Les snapshots pré-099 restent lisibles et incomplets. Les champs legacy restent non autoritatifs. Aucun fallback, backfill ou donnée P02 n’est utilisé.
