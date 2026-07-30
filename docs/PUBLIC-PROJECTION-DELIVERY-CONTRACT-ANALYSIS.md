# Public Projection Delivery Contract — analyse

## Événements audités

- **ListingLifecycle** : `ListingEvent` expose listingId, revisionId, contexte de transition, occurredAt, aggregateVersion et eventIndex. Les transitions publié, expiré, archivé, suspendu et retiré affectent directement la page publique. Actor, raison et origine ne sont pas requis dans le payload de reconstruction.
- **RealEstateCatalog** : `PropertyEvent` expose propertyId, occurredAt, aggregateVersion et eventIndex. Enregistrement, mise à jour, adresse, surface et archivage peuvent invalider la projection.
- **Media** : `MediaCollectionEvent` expose collectionId, occurredAt, aggregateVersion et eventIndex. Ajout, retrait, archivage, ordre et média principal affectent la projection ; checksum source, caption et détails internes ne doivent pas être copiés.
- **SearchDiscovery** : `SearchIndexEvent` expose indexId, documentId, listingId, occurredAt, aggregateVersion et eventIndex. Indexation, mise à jour, retrait, rebuild et visibilité peuvent déclencher une reconstruction.
- **ContentSeo** : `SeoEvent` expose projectionId, listingId, occurredAt, aggregateVersion et eventIndex. Génération, mise à jour, fail-safe et canonical changée affectent la projection. Le matériau SEO complet n'est pas recopié.
- **AdministrationAudit** : événements correctement versionnés, mais non nécessaires à la reconstruction publique actuelle ; ils restent hors catalogue.

## Écarts et sécurité

Les identités portent des Value Objects différents par module. Listing expose des données d'acteur et de modération ; ContentSeo transporte parfois un `SeoMaterial` complet ; Media possède des checksums. Ces données restent internes. Aucun événement métier n'est accepté implicitement : des adapters applicatifs futurs devront produire l'un des cinq faits fermés du catalogue.

Les versions existantes sont positives et `eventIndex` commence à 1. Toutes les interfaces utiles exposent occurredAt, mais cette date ne participe pas à l'ordre. Correlation et causation n'existent pas uniformément ; elles restent facultatives et appartiennent à l'enveloppe delivery.

## Périmètre retenu

Le catalogue initial contient cinq types de reconstruction, un par source : Listing, Property, MediaCollection, SearchIndex et SeoProjection. Chaque payload DTO ne transporte que l'identité nécessaire. L'enveloppe transporte déjà module, type/id/version d'Aggregate et eventIndex. Ce choix minimise les données, évite de figer les Domain Events dans un protocole externe et permet à l'Updater de relire les sources autorisées.

## Contrats manquants comblés

Le sprint ajoute identités spécialisées, enveloppe immutable, clé idempotente canonique, ordre local, payloads DTO, checksum, catalogue fermé, compatibilité, factory de message, port consommateur, résultats et statuts conceptuels. Il ne crée ni adapter de Domain Event, ni Outbox, ni transaction, ni runtime.
