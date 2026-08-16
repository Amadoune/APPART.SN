# Business Year Authority — Blueprint 01

## Décision V1

`BusinessYearAuthorityV1`, owner RealEstateCatalog Application, résout par fonction pure l'année civile UTC de l'instant métier stable de la commande Property.

`BusinessYear = integer(occurredAt converti en UTC, format Y)`.

L'instant est explicite, immutable et couvert par l'identité de commande. Aucune clock courante, configuration, donnée HTTP métier, Projection ou Search n'intervient.

Cette autorité commune sert `RegisterProperty` et `UpdateProperty`. **GO PROPOSÉ.**
