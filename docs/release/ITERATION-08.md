# RC2 Stabilization — Iteration 08

## Périmètre

Cette itération traite exclusivement `UnderReview → ApprovePublicationV1 → ListingPublicationCommandGateway → ApproveAndPublish → Published`.

Projection, Search et Public Listing ne sont pas qualifiés.

## Première divergence

Le premier ApprovePublication retournait HTTP 503. La transaction annulait intégralement la tentative : Workflow et Aggregate restaient `UnderReview`, la Queue restait `claimed` et aucun ledger Approve n'était conservé.

La Gateway atteignait `PublishListing`, mais cette instance utilisait le `PropertyCatalog` Registry historique. Pour le Property Authoring réel :

- le catalogue historique répondait `unavailable` ;
- `PropertyAuthoringCatalogAdapter` répondait `eligible`.

La publication échouait donc avant la transition Aggregate.

## Correction unique

Le Provider de la Gateway fournit contextuellement à `PublishListing` le `PropertyAuthoringCatalogAdapter` certifié, avec les mêmes Registry, MediaCatalog, Public Facts et transaction existants.

Aucune règle de publication, donnée Property, donnée Media, Queue, Claim ou commande BeginReview n'est modifiée.

## Rejeu

Listing réel : `f2f04694-6db3-4c0b-b884-d3520556bf83`.

| Contrôle | Résultat |
|---|---|
| ApprovePublication initial | HTTP 200 |
| Workflow | `published`, version 4 |
| Aggregate Listing | `published`, version 3 |
| Queue | `completed`, version 4 |
| Ledger Gateway | `approve_and_publish` / `applied` |
| Replay ApprovePublication | HTTP 409 |

Le replay HTTP 409 est la première divergence suivante. Conformément à la règle d'une seule correction, elle n'est ni corrigée ni analysée davantage dans cette itération.

## Limite

La surface HTTP compose actuellement l'activation de Projection après Approve. Son exécution n'est pas une qualification de Projection dans cette itération. Aucun contrôle Search ou Public Listing n'a été effectué.

## Verdict

**NO GO PROPOSÉ — ITERATION 08**

ApprovePublication et Published sont démontrés, mais le critère de replay n'est pas satisfait.
