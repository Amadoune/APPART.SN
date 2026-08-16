# Primary Media authority

Le domaine Media est l'autorité : `MediaCollection::primary()`, `markPrimary()` et `MediaMarkedPrimary`. Le premier ajout devient primary par règle d'aggregate; retrait/archivage du primary exige un remplacement.

La décision publique doit utiliser ce primary s'il est publiquement éligible. Elle ne doit jamais sélectionner arbitrairement le premier item. La résolution du cas primary actif mais non livrable dépend de l'autorité préalable demandée.
