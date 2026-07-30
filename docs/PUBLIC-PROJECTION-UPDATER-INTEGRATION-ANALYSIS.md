# Public Projection Updater Integration — analyse

## Audit des informations et responsabilités

Le DeliveryMessage contient identité, eventType/version, module, Aggregate, ordre causal et payload minimal. Il ne contient volontairement ni les Aggregates complets, ni Search/SEO, ni canonical, ni sources publiques Geography/Media. Ces informations doivent être relues par les sources autorisées déjà possédées par `PublicListingProjectionUpdater`.

L'Updater figé accepte un `listingId` et possède son port `PublicListingProjectionSource`. Il construit Search, SEO, ReadModel et watermark, puis écrit exclusivement via `PublicListingProjectionWriter`. Le Consumer ne doit donc jamais recevoir ni reproduire ces responsabilités.

## Intégration retenue

`PublicProjectionSourceResolver` valide le message contre le catalogue et délègue la résolution de cible/readiness à `PublicProjectionSourceLookup`. Il distingue Resolved, SourceUnavailable, PromotionNotReady, WatermarkIncomplete et l'absence explicite des révisions Public Geography/Public Media.

`PublicListingProjectionUpdaterExecutor` est l'unique adaptateur vers l'Updater figé et ne fait que déléguer `update(listingId)`. `PublicProjectionUpdaterConsumer` enchaîne Resolver puis Executor et transforme exhaustivement le résultat Updater en résultat Delivery.

Le lookup concret et ses bindings restent réservés au Runtime : aucun Repository Aggregate, PDO, SQL ou Laravel n'est introduit ici.
