# Property Lifecycle Worker Registration Matrix

| Famille | Couples type/version | Consumer |
|---|---:|---|
| Reconstruction historique | 5 | `PublicProjectionUpdaterConsumer` |
| Listing Publication | 15 | `ListingPublicationEventDeliveryConsumer` |
| Property Lifecycle | 7 | `PropertyLifecycleEventDeliveryConsumer` |

Le registre contient 27 couples uniques. Les sept types Property proviennent de `PropertyLifecycleEventType::cases()` en version 1. Le Consumer est un singleton Laravel paresseux. Le bootstrap ne lit aucun message, ne restaure aucun événement et n'appelle aucun routeur.
