# Final Catch-up Model

`CatchUpContentSeoSnapshotV1` reçoit uniquement ListingId et délègue au même matérialiseur que Published.

Il utilise les mêmes readers, canonical policy, policyId, decisionAt Published, règles de révision/version, snapshotId et writer. Aucun SQL direct, fixture, branche RC2 ou temps courant.

RC2 est rattrapable immédiatement après Implementation GO.
