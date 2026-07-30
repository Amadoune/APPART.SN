# Public Projection Updater Integration

## Rôle et pipeline

Le pipeline certifié est : DeliveryMessage → SourceResolver → PublicListingProjectionUpdater → PublicListingProjectionWriter/Store. Le Consumer est purement applicatif et retourne uniquement `PublicProjectionDeliveryConsumptionResult`.

Le Resolver valide type, version, module, Aggregate et payload avec le catalogue fermé. Le lookup autorisé fournit une cible listing et un état de readiness. Une source absente, une promotion non prête, un watermark incomplet ou l'absence de révision stable Geography/Media bloque avant tout appel Updater.

## Mapping exhaustif

| Résultat Updater | Résultat Delivery |
|---|---|
| Applied | Consumed |
| AlreadyApplied | AlreadyConsumed |
| RejectedObsolete | RejectedObsolete |
| SourceUnavailable, ProjectionUnavailable, PromotionNotReady, IncompleteWatermark | BlockedBySourceReadiness |
| DivergentWatermark | DivergentPayload |
| CanonicalCollision, CanonicalReplacementRequired, HistoricalReservationConflict, GenerationMismatch | PermanentFailure |

Aucune branche par défaut n'existe.

## Intégration Worker et invariants

Le Consumer est enregistré explicitement dans le registre existant par consumerId, eventType et payloadVersion. Le Worker reste inchangé. Applied devient Delivered dans l'Outbox ; AlreadyApplied permet la redelivery idempotente après crash. Les blocages readiness restent durables et ne perdent aucun message.

Le Consumer ne construit aucun Search, SEO ou ReadModel, ne décide aucune canonical, n'écrit aucun SQL et ne connaît ni PDO, Laravel, Repository Aggregate ou Store concret. Toute reconstruction passe exclusivement par l'adaptateur du `PublicListingProjectionUpdater`.

## Préparation Runtime

Le Runtime futur devra fournir un `PublicProjectionSourceLookup` concret, composer l'Updater avec ses sources et son Writer PostgreSQL, puis enregistrer le Consumer. Aucun binding, Service Provider, Job, Queue, scheduler, Controller, route ou HTTP n'est créé dans ce sprint.
