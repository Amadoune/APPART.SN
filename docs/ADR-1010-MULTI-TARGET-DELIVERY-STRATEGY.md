# ADR-1010 — Multi-target Delivery Strategy

Statut : proposé par le Sprint 3.9B.

## Décision

Retenir l'Option B : résolution multi-cibles paginée dans la consommation du message source. Le
message Property ou Media demeure l'unité durable suivie par l'Outbox et le Worker. Une couche de
résolution normalise explicitement Media vers sa Property, puis la stratégie 3.9B produit des pages
ordonnées de ListingId au moyen du port 3.9A.

Le futur Consumer ne marquera le message source consommé qu'après la dernière page et après le
succès de toutes ses cibles. Un crash provoque la redelivery du message source. Les pages et leur
ordre sont déterministes ; les cibles déjà appliquées convergent vers `AlreadyApplied` grâce à
l'idempotence certifiée de l'Updater.

## Options écartées

| Option | Décision | Motif |
|---|---|---|
| A — fan-out durable | rejetée | impose une nouvelle identité causale et une persistance de fan-out ; les contraintes Outbox actuelles ne modélisent pas ces enfants sans ambiguïté |
| B — résolution multi-cibles | retenue | conserve l'unité durable, le retry et l'ordre source existants ; la redelivery est sûre |
| C — fan-out en amont | rejetée | impose au producteur une résolution inter-module et couple la transaction locale aux Listings |
| D — coordinateur séparé | rejetée | duplique l'état durable et la mécanique de claim/retry déjà certifiée |

## Limite du sprint

3.9B définit le contrat et la stratégie. Il ne modifie ni le Consumer ni
`PublicProjectionSourceResolution`. Leur évolution appartient au sprint suivant.
