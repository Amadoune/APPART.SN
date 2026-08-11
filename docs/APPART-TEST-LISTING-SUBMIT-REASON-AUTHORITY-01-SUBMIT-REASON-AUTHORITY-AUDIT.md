# Listing Submit Reason Authority 01 — Audit

## Verdict

`NO GO PROPOSÉ`

Le command Submit contient intentId, acteur, Listing, version et instant, mais aucun reason. L'intentId identifie un replay ; il n'exprime pas pourquoi le Submit est demandé.

Les deux requests authoring interdisent les champs inconnus et ne déclarent aucun reason pour RequestSubmission/SubmitListing. `ListingTransitionPolicy` qualifie trigger et origin, pas la motivation.
