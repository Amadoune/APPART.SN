# Final Published Handoff

Le consumer productif futur reçoit `ListingPublished` et transmet uniquement ListingId à `MaterializeContentSeoSnapshotV1`. Le caller ne fournit ni canonical, ni indexabilité, ni snapshotId, ni version.

Le matérialiseur relit les sources owner, requiert Search Found, construit le snapshot et appelle le writer. Applied/AlreadyApplied ferment le handoff ; source missing attend un replay ; corruption/divergence échouent fermées ; indisponibilité est retryable.

Search et ContentSeo conservent des transactions locales distinctes.
