# Replay Strategy

## Ingestion

L'ingestion réserve `queueItemId` sous transaction locale PublicationReview :

1. verrou advisory transactionnel sur `queueItemId` ou mécanisme équivalent ;
2. insertion conditionnelle par identité canonique ;
3. même événement et même checksum : `AlreadyApplied` ;
4. même identité et checksum divergent : `Corrupted` ou `DivergentMessage`, jamais fallback ;
5. commit local indépendant du consumer Projection.

## Claim

Une commande de claim porte au minimum `commandId`, `queueItemId`, `actor`, `occurredAt` et `expectedVersion`.

La mutation réserve d'abord `commandId` avec un checksum canonique de la commande, puis verrouille l'item :

- commande déjà complétée avec checksum identique : résultat mémorisé / `AlreadyApplied` ;
- même `commandId` avec checksum différent : conflit divergent ;
- version différente : `VersionConflict` ;
- item disponible : passage atomique à `claimed` et incrément de version ;
- item déjà réclamé par le même acteur via la même commande : `AlreadyApplied` ;
- item réclamé par une autre commande : `Conflict`.

## Concurrence

La sélection concurrente utilise un claim transactionnel (`FOR UPDATE SKIP LOCKED` ou garantie équivalente). Le verrou de sélection ne remplace pas `expectedVersion`. Les deux protections sont nécessaires : l'une répartit le travail, l'autre protège la mutation adressée.

## Replay des commandes Lifecycle

BeginReview et ApprovePublication possèdent leurs propres `commandId`. PublicationReview conserve leur résultat fermé et délègue une seule intention logique au Lifecycle. Un retry ne fabrique ni nouvelle transition ni nouvelle preuve ; il retourne le résultat mémorisé ou la réduction mécanique du `AlreadyApplied` Lifecycle.

## Projection après publication

ProjectPublishedListing possède une identité de commande indépendante. Il ne marque l'item comme projeté qu'après succès autoritatif de Public Projection. Un échec laisse une reprise locale possible sans réexécuter ApproveAndPublish.

## Rollback externe

Chaque opération utilise une transaction locale ou un savepoint lorsqu'elle rejoint une transaction existante. Elle ne commit ni ne rollback une transaction appartenant à l'appelant.
