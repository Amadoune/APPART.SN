# Listing Publication Worker Registration Matrix

| Famille | Nombre | Consumer | Version |
|---|---:|---|---:|
| Reconstruction historique | 5 | `PublicProjectionUpdaterConsumer` | 1 |
| Listing Publication | 15 | `ListingPublicationEventDeliveryConsumer` | 1 |

Le registre de production contient vingt couples type/version uniques pour le même consumer id Runtime. Les quinze types proviennent directement de `ListingPublicationEventType::cases()`.

La construction du registre est paresseuse. Elle ne lit aucun message, ne restaure aucun événement et n'appelle aucun routeur.
