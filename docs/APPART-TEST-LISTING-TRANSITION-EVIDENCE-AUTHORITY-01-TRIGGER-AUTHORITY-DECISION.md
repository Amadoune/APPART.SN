# Listing Transition Evidence Authority 01 — Trigger Authority

La correspondance est déjà une règle Listing Lifecycle :

- Draft→Submitted : `SubmissionConfirmed` ;
- Submitted→UnderReview : `ReviewStarted` ;
- UnderReview→Published : `FavorableReview`.

Les origins autorisées sont respectivement Advertiser, Moderation et Moderation. Cette autorité réside dans `ListingTransitionPolicy`, non dans HTTP ou un composite.
