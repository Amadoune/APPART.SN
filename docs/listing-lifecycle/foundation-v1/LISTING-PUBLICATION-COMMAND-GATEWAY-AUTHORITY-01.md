# Listing Publication Command Gateway Authority 01

## Décision

L'autorité légitime est une Gateway owner-scoped appartenant à **Listing Lifecycle** : `ListingPublicationCommandGatewayV1`.

Elle constitue l'unique entrée des intentions PublicationReview `BeginReview` et `ApproveAndPublish`. Elle ne reçoit aucune donnée de publication déjà résolue ; elle les obtient exclusivement auprès des autorités certifiées, puis exécute workflow, Aggregate, Registry et événements dans une transaction locale unique.

## Chaîne normative

`PublicationReview intention → ListingPublicationCommandGatewayV1 → authorities → workflow + Aggregate → Registry + events`

PublicationReview exprime seulement l'intention et son identité. Listing Lifecycle reste responsable de la validité et de l'application de la transition.

## Responsabilités de la Gateway

1. réserver/rejouer l'identité de commande ;
2. relire le workflow et l'Aggregate Listing ;
3. vérifier leur paire d'états certifiée ;
4. résoudre mécaniquement les autorités nécessaires ;
5. appeler `SendToReview` ou `PublishListing` sans dupliquer leurs règles ;
6. appliquer la transition workflow correspondante dans la même transaction ;
7. persister Registry, workflow, public facts et événements atomiquement ;
8. réduire le résultat vers un catalogue fermé.

## Invariants

- aucun succès si workflow et Aggregate divergent ;
- aucun événement `Published` sans Aggregate réellement publié ;
- aucune valeur Media, Property, expiration, révision ou evidence fournie par PublicationReview ;
- aucune projection ou écriture Search dans la Gateway ;
- même commande et même checksum : replay déterministe ;
- même identité et contenu divergent : conflit explicite.

## Verdict

**GO PROPOSÉ — APPART.SN LISTING LIFECYCLE FOUNDATION — LISTING PUBLICATION COMMAND GATEWAY AUTHORITY 01**
