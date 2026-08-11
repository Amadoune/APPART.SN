# Publication Review Foundation v1 — Discovery 01

## Finalité

La capacité `PublicationReview` coordonne exclusivement le passage contrôlé d’un Listing `Submitted` à `Published`. Elle ne possède ni les règles du Listing Lifecycle, ni les décisions IAM, ni les modèles publics.

## Chaîne qualifiée

`ListingSubmitted → Publication Review Queue → BeginReview → UnderReview → ApproveAndPublish → Published → Public Projection Update`

## Foundation minimale

La future Foundation devra matérialiser uniquement :

1. une source owner-scoped des candidatures de publication alimentée par les événements Listing Lifecycle ;
2. un Reader V1 paginé et déterministe de la file de revue ;
3. une commande `BeginReview` owner-scoped ;
4. une commande `ApproveAndPublish` owner-scoped ;
5. un contrat IAM d’autorisation spécifique à Publication Review ;
6. une orchestration post-publication idempotente vers `PublicListingProjectionUpdater` ;
7. des Results/Status fermés, des identités de commande, optimistic locking et replay déterministe.

## Exclusions

La Foundation ne traite pas les reports, cases, findings ou décisions de `ModerationReports`. Elle ne modifie pas Listing Lifecycle, Search ou Public Projection et ne contient aucune UI P08.

## État observé

Les règles de transition et les use cases `SendToReview` / `PublishListing` existent. Les surfaces de composition, de découverte de la file, d’autorisation dédiée et d’activation aval sont absentes.
