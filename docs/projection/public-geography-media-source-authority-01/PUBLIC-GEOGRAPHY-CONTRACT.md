# Public Geography contract

Le contrat consommé est `PublicGeographyDecisionReader::read(placeId): PublicGeographyReadResult`.

Résultats fermés : `Found`, `Missing`, `Corrupted`. `Found` porte `PublicGeographyDecision` : `placeId`, révision positive, `locality`, breadcrumb ordonné de couples label/URL. Le store productif est `public_geography.decisions`; le reader est `PostgreSqlPublicGeographyReader`.
