# Public Projection Delivery Contract

## Rôle et ownership

Ce contrat transforme un fait explicitement publiable en message durable potentiel, puis définit son interprétation par un futur consommateur. Le module source reste propriétaire du fait ; Public Projection Delivery possède l'enveloppe, le catalogue et le protocole. L'Outbox future possédera seulement l'état de livraison.

## Enveloppe et identités

`PublicProjectionDeliveryMessage` est readonly et contient messageId, idempotencyKey, eventType, payloadVersion, sourceModule, aggregateType/id, ordre version/index, occurredAt, recordedAt, payload et traces facultatives. Les dates sont informatives.

Tous les Value Objects sont spécialisés. `eventIndex` et payloadVersion commencent à 1 ; aggregateVersion est positive. Aucune normalisation silencieuse d'identité n'est appliquée.

## Idempotency key

La stratégie retenue est la chaîne canonique lisible avec chaque segment encodé `rawurlencode`, séparé par `:` :

`sourceModule:aggregateType:aggregateId:aggregateVersion:eventIndex:eventType:payloadVersion`

Elle est vérifiable en PostgreSQL, sans sel, timestamp ou hash opaque. Les composantes originales restent dans l'enveloppe. Le messageId stable est préfixé à partir de cette clé. Un checksum SHA-256 porte uniquement sur la sérialisation JSON canonique du payload et sert à détecter une divergence sous la même clé.

## Ordre causal

`PublicProjectionDeliveryOrder` contient aggregateVersion et eventIndex. Sa relation explicite est Equal, Before, After, Gap ou Invalid. `Invalid` représente notamment une tentative de comparer deux Aggregates distincts. Aucun ordre global n'existe et aucune date, identité de message ou insertion SQL n'intervient.

## Catalogue et payloads

Le catalogue fermé supporte en version 1 :

| eventType | module | Aggregate | payload |
|---|---|---|---|
| `listing.reconstruction.requested` | ListingLifecycle | Listing | listingId |
| `property.reconstruction.requested` | RealEstateCatalog | Property | propertyId |
| `media.reconstruction.requested` | Media | MediaCollection | mediaCollectionId |
| `search.reconstruction.requested` | SearchDiscovery | SearchIndex | listingId |
| `content_seo.reconstruction.requested` | ContentSeo | SeoProjection | listingId |

Chaque payload est un DTO readonly à champs fixes. Aucun array libre n'entre dans la factory. Type, version, module, Aggregate et classe de payload doivent correspondre à une entrée explicite.

## Producteur et consommateur

`PublicProjectionDeliveryMessageFactory` accepte seulement `PublicProjectionDeliveryPublishableFact`. La factory de catalogue conserve version/index, dates et traces, calcule les identités et rejette un fait absent du catalogue. Elle n'écrit, ne charge et ne publie rien.

`PublicProjectionDeliveryConsumer` retourne un résultat spécialisé : Consumed, AlreadyConsumed, RejectedObsolete, BlockedBySequenceGap, BlockedBySourceReadiness, UnsupportedEventType, UnsupportedPayloadVersion, DivergentPayload, RetryableFailure ou PermanentFailure.

## Statuts et transitions conceptuelles

Pending peut devenir Claimed ou Quarantined. Claimed peut devenir Delivered, RetryScheduled, l'un des deux états Blocked, ou Quarantined. RetryScheduled et les états Blocked peuvent redevenir Claimed ou être quarantinés. Delivered et Quarantined sont terminaux dans ce contrat. Attempts, bail et dates de statut appartiennent au prochain sprint.

## Compatibilité

Le catalogue répond Supported, DeprecatedButSupported, UnsupportedVersion ou UnsupportedType. Une version inconnue n'est jamais interprétée. Ajout obligatoire, renommage, suppression requise ou changement de sens imposent une nouvelle version/type. Un ajout facultatif n'est compatible que si le DTO et le catalogue le déclarent explicitement.

## Sécurité et invariants

Aucun Aggregate, secret, token, credential, checksum média, acteur ou motif de modération n'est transporté. La sérialisation canonique trie les clés et conserve les types scalaires. Même causalité implique même clé ; dates différentes n'y changent rien ; même clé et checksum différent impliquent DivergentPayload.

## Limites et étape suivante

Le catalogue ne contient pas encore Geography/Public Media, faute de révision stable. Aucun adapter de Domain Event, stockage, statut persistant, attempt, claim, retry, worker ou Laravel n'existe. Le prochain Transactional Outbox Contract pourra réutiliser l'enveloppe, les statuts et la suite contractuelle pour définir journal, claiming et accusés, sans rouvrir l'identité ou l'ordre causal.
