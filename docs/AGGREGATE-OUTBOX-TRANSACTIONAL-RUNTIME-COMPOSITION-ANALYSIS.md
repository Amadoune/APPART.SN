# Aggregate/Outbox Transactional Runtime Composition — analyse

## Cause traitée

Le Repository Listing résolu par Laravel utilisait sa transaction locale par défaut. Dans `PostgreSqlAggregateOutboxTransaction`, cette configuration tentait une transaction PDO imbriquée avant l'append Outbox.

## Composition retenue

Le Provider réutilise exclusivement :

- `PostgreSqlAggregateOutboxTransaction` comme propriétaire du commit ou rollback ;
- `PostgreSqlAggregateOutboxParticipantTransaction` comme participant sans transaction imbriquée ;
- la même instance PDO issue de la connexion Laravel `pgsql`.

Les Repositories Listing, Property et Media reçoivent explicitement le participant certifié. Leur code, leurs contrats et leurs mappers restent inchangés.

## Audit des producteurs

- Listing : concerné, car le catalogue Delivery expose `listing.reconstruction.requested` et le Repository possède `ListingTransaction`.
- Property : concerné, pour `property.reconstruction.requested` et `PropertyTransaction`.
- Media : concerné, pour `media.reconstruction.requested` et `MediaCollectionTransaction`.
- Search : pas de Repository Aggregate transactionnel dans ce chemin ; le writer durable détecte déjà une transaction PDO externe.
- Content/SEO : même situation que Search ; le snapshot writer participe directement à la transaction PDO externe lorsqu'elle existe.

Aucun binding préventif n'est ajouté à Search ou Content/SEO.
