# Property Lifecycle Event Transport Analysis

## Frontière

`PropertyLifecycleDeliveryPayload` est l'unique adaptateur entre l'événement métier 4.2E et le contrat technique `PublicProjectionDeliveryPayload`. Il encapsule le JSON canonique sans recalculer de transition ou d'événement.

La restauration reconstruit les Value Objects certifiés, redérive l'identité métier, puis resérialise l'événement. Elle réussit uniquement si la représentation obtenue est identique byte-for-byte à l'entrée.

## Intégrité

Le checksum est SHA-256 du champ `canonicalEvent` exact. Toute différence de forme, type, ordre, identité, version, métadonnée ou sérialisation est rejetée par `PropertyLifecycleEventTransportException`.

## Périmètre

Le sprint ne fournit ni destination, ni implémentation de routeur, ni Consumer, Outbox, Worker, binding ou migration. Il définit uniquement le payload et le port de routage futur.
