# Public Projection Runtime Certification — analyse finale

## Résultat

La chaîne complète est désormais exécutable depuis le conteneur Laravel de production. Les blocages Worker (3.6H) et participation transactionnelle Aggregate/Outbox (3.6I) sont levés.

## Preuve principale

Le scénario Listing :

1. résout le Repository et la transaction depuis Laravel ;
2. persiste la mutation et le message Outbox dans un commit commun ;
3. vérifie le message `pending` après commit ;
4. résout et exécute le Worker Laravel ;
5. traverse registre, Consumer, Lookup, sources, Updater et Store ;
6. vérifie l'acknowledgement `delivered` ;
7. relit le ReadModel durable par `PublicListingQuery` ;
8. obtient HTTP 200 avec exactement le même ReadModel ;
9. confirme Runtime Health `Healthy` ;
10. rejoue une reconstruction causale et converge vers `AlreadyConsumed` sans seconde projection.

## Multi-target

Le scénario Property/Media prépare 101 Listings réels liés à la même Property. La pagination Runtime certifiée est limitée à 100 : l'événement Property traverse donc réellement deux pages. L'événement Media résout ensuite MediaCollection → Property → les mêmes 101 Listings et converge entièrement vers `AlreadyConsumed`.

Aucun Repository, Worker, transaction, Consumer, source, Updater ou Store n'est construit manuellement.
