# Boundary Audit

## Publication Review

`PublicationReview` est l’owner applicatif de la file et de l’orchestration. Il observe les faits Listing Lifecycle et adresse ses commandes publiques sans reproduire les règles de transition.

### Dépendances autorisées

- Listing Lifecycle Events et commandes owner-scoped certifiées ;
- Listing Revision Authority ;
- Transition Evidence composition qualifiée ;
- IAM Publication Review Authorization ;
- Public Listing Projection Updater après état `Published` confirmé.

### Dépendances interdites

- tables Listing ou Projection lues directement ;
- Aggregate Listing lu ou muté depuis HTTP/UI ;
- Moderation Reports Queue ;
- déduction d’un rôle IAM depuis le rôle générique `moderator` ;
- écriture directe dans Search ou la Projection publique ;
- publication implicite après Submit.

## Séparation avec Report Moderation

| Publication Review | Report Moderation |
|---|---|
| candidature issue de `ListingSubmitted` | signalement explicite d’un contenu ou compte |
| objectif : qualifier une première publication | objectif : traiter un risque ou une violation |
| actions : BeginReview, ApproveAndPublish | actions Listing : suspend, request_changes, reject, archive |
| file des candidatures de publication | file des cases de signalement |
| autorité de publication dédiée | rôle `moderator` actuellement qualifié pour les reports |

Ces flux ne partagent ni queue item, ni case, ni décision, ni catalogue d’actions.

## Projection

`ApproveAndPublish` doit d’abord obtenir un résultat terminal `Published`. Ensuite seulement, une étape idempotente appelle le Projection Updater avec le `ListingId`. Search et Public Listing restent de simples consommateurs de la projection activée.
