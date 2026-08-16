# RC2 Stabilization — Iteration 02

## Périmètre

Cette itération traite exclusivement l'ingestion `Submitted → Publication Review Queue`. Aucun Claim, BeginReview, Approve, Projection ou Search n'a été analysé ou exécuté.

## Première divergence

Le Submit public appelait directement `ListingPublicationOrchestrator`. Le workflow atteignait `Submitted`, mais cette entrée ne produisait aucun `ListingSubmitted`; `PublicationReviewConsumer` et `PublicationReviewQueue::ingest()` n'étaient donc jamais appelés.

La première composition corrigée a également révélé que l'orchestrateur événementiel, propriétaire de la transaction Workflow + Outbox + Queue, ne peut pas être imbriqué dans la transaction Authoring. La composition finale prépare le snapshot idempotent, puis délègue la transition à l'orchestrateur événementiel qui possède sa transaction autoritative.

## Correction unique

Le chemin `SubmitListing` de `DeterministicPropertyListingAuthoringOperations` dépend désormais de `ListingPublicationEventOrchestrator` et lui transmet la transition Submit avec ses métadonnées temporelles canoniques.

Aucun changement n'est apporté à Listing Lifecycle, à la Queue, au consumer, à sa persistence ou à son Reader.

## Preuve terminale

Le rejeu réel a produit le Listing `123f9e41-1be9-419f-a92b-b247a04e8ee9` :

`HTTPS → Owner Login → Upload HTTP 201 → Preview → Submit HTTP 200 → Submitted → Reviewer Login → Authorization HTTP 200 → Queue`

Une unique carte Queue portant le préfixe `123f9e41` a été observée. La campagne s'est arrêtée immédiatement avant Claim.

Preuves externes : `RC2-STABILIZATION/iteration-02/ITERATION-02-MANIFEST.json`, `04-queue.png` et `TRACE.zip`.

## Verdict

**GO PROPOSÉ — ITERATION 02**
