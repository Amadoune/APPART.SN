# Reservation Strategy

## 1. Statut et objet

- **Phase :** 2
- **Sprint :** 2.3 — Repository Contract Foundation
- **Version :** 1.0
- **Date :** 17 juillet 2026
- **Statut :** normatif

Ce document fixe les identités et clés dont l’unicité doit être réservée pour les quatorze Aggregate Roots. Il complète le Repository Protocol, la Transaction Policy et l’Unit of Work sans définir de stockage, schéma ou commande SQL.

## 2. Règles générales

1. Une réservation possède un module propriétaire et, lorsqu’elle appartient à un Root, l’identité canonique de ce Root.
2. L’ajout d’un Aggregate, son état initial et toutes ses réservations obligatoires sont atomiques.
3. Une mutation introduisant une nouvelle identité enfant ou clé historique utilise une opération atomique de sauvegarde avec réservation.
4. Un rollback retire toutes les réservations créées par la tentative. Il ne libère jamais une réservation déjà validée.
5. Une réservation durable n’est pas libérée par suspension, archivage, retrait, fusion, fermeture, expiration ou suppression d’une projection.
6. Une clé réutilisable ne l’est qu’après expiration déterministe de sa fenêtre normative ; aucune purge technique ne décide de la réutilisation.
7. Les identités internes à un Aggregate sont uniques dans ce Root. Elles ne deviennent globales que lorsque la matrice ci-dessous le déclare.
8. Une même clé ne peut changer de propriétaire. Une nouvelle tentative du même propriétaire n’est idempotente que si son contrat le prévoit explicitement.

## 3. Matrice normative

| Module / Aggregate Root | AggregateId | Business Keys | Child Identities | Atomic Reservations | Reuse Policy | Rollback | Lifetime | Ownership |
|---|---|---|---|---|---|---|---|---|
| AdministrationAudit / `AdministrativeAction` | `AdministrativeActionId` | aucune clé globale supplémentaire | `ApprovalId`, `DecisionId`; AuditEntry sans identité propre | AggregateId à `add`; identités enfant uniques dans le Root lors de la mutation | jamais pour AggregateId ni identités enfant | aucun état, ID ou preuve de la tentative ne devient durable | permanente, y compris action rejetée | AdministrationAudit ; enfants possédés par AdministrativeAction |
| ContactsLeads / `Lead` | `LeadId` | `LeadSignature` + instant selon `LeadDeduplicationClaim` | entrées d’historique sans identité propre | AggregateId et claim de déduplication dans le même `add` | AggregateId jamais ; signature réutilisable uniquement hors fenêtre glissante définie par `LeadDeduplicationPolicy` | ni Lead ni claim visible | ID permanent ; claim conservé au moins jusqu’à expiration de sa fenêtre et durée d’audit | ContactsLeads ; claim rattaché au Lead créé |
| ContentSeo / `SeoProjection` | `SeoProjectionId` | `ListingId` source ; chaque `CanonicalUrl` courant ou historique | entrées d’historique canonical sans ID autonome | AggregateId + ListingId + canonical initial à `add`; chaque nouveau canonical avec `save(expectedVersion)` | AggregateId et ListingId jamais tant que l’ownership existe ; CanonicalUrl **jamais**, conservée pour redirection | projection et nouvelles réservations canonical annulées ensemble | permanente ; canonical historique permanent même après retrait/fail-safe | ContentSeo ; un ListingId et ses canonicals appartiennent à une SeoProjection |
| Geography / `Place` | `PlaceId` | couple (`CountryCode`, `PlaceCode`) | aliases et divisions internes sans identité globale | AggregateId + couple pays/code à `add` | jamais, y compris lieu désactivé ou fusionné | ni Place ni code visible | permanente | Geography ; code officiel possédé par Place |
| IdentityAccess / `Account` | `AccountId` | `EmailAddress`, `PhoneNumber` | vérifications, rôles et consentements identifiés par leur clé métier locale ; tokens non réattribuables | AggregateId + email + téléphone à `add` | jamais automatiquement ; suspension ou remplacement de vérification ne libère rien | aucune identité de compte visible | permanente ; secrets/tokens suivent leur rétention de sécurité mais ne sont jamais réattribués | IdentityAccess ; email/téléphone possédés par Account |
| ListingLifecycle / `Listing` | `ListingId` | aucune clé globale supplémentaire ; `PropertyId` est une référence, pas une réservation de propriété | `ListingRevisionId` | AggregateId à `add`; RevisionId unique dans le Root lors de chaque transition concernée | jamais | aucune Listing ni révision partielle | permanente, y compris archive/retrait/expiration | ListingLifecycle ; révisions possédées par Listing |
| Media / `MediaCollection` | `MediaCollectionId` | aucune clé globale ; checksum et ordre sont des invariants locaux | `MediaId` | AggregateId à `add`; MediaId avec `saveWithMediaReservation` | jamais, même après retrait ou archivage ; checksum/ordre réutilisables seulement dans la collection selon les invariants courants | collection, mutation et MediaId annulés ensemble | permanente pour IDs ; checksum/ordre durent avec leur présence dans le Root | Media ; MediaId possédé définitivement par MediaCollection |
| ModerationReports / `ModerationCase` | `ModerationCaseId` | aucune clé globale supplémentaire ; `ListingId` est une référence | `ReportId`, `FindingId`, `DecisionId` | case + premier ReportId via `addWithReportReservation`; autres IDs via opérations `saveWith…Reservation` | jamais | case/mutation et identité de preuve annulées ensemble | permanente pour toutes les preuves, même case close | ModerationReports ; chaque preuve possédée définitivement par ModerationCase |
| MonetizationPayments / `Order` | `OrderId` | aucune ; ProductId dans une ligne est une référence snapshotée | lignes et bénéfices sans identité autonome | AggregateId à `add` | jamais, y compris remboursement/expiration | aucune Order visible | permanente pour audit financier | MonetizationPayments ; bénéfices et historique possédés par Order |
| MonetizationPayments / `Payment` | `PaymentId` | `TransactionReference` | tentatives sans identité globale distincte | PaymentId + TransactionReference via `registerIdempotently` | jamais ; même référence + même intention retourne l’existant, même référence + intention différente est un conflit | ni paiement ni référence visibles | permanente pour audit et idempotence | MonetizationPayments ; référence possédée par Payment |
| MonetizationPayments / `Product` | `ProductId` | aucune clé métier globale actuelle ; le nom n’est pas une identité | aucune | réservation administrative de ProductId lors de la publication catalogue, hors Registry mutable | jamais ; retrait du catalogue ne permet pas la réattribution | aucune entrée catalogue partielle | permanente afin que les OrderLine historiques restent interprétables | MonetizationPayments ; catalogue administré conformément à l’ADR Product |
| Professionals / `Professional` | `ProfessionalId` | `RegistrationNumber` | `EstablishmentId`, `MandateId` (local au Root) | AggregateId + RegistrationNumber à `add`; EstablishmentId via `saveWithEstablishmentReservation`; MandateId unique dans le Root | jamais, y compris établissement retiré, mandat révoqué ou professionnel suspendu | mutation et réservation enfant annulées ensemble | permanente | Professionals ; EstablishmentId possédé définitivement par Professional |
| RealEstateCatalog / `Property` | `PropertyId` | `PropertyReference` | `AddressId` local au Root | AggregateId + PropertyReference à `add`; AddressId validé dans le snapshot du Root | jamais, y compris Property archivée ; AddressId jamais réattribué dans le Root | aucune propriété/référence/adresse partielle | permanente | RealEstateCatalog ; référence possédée par Property, Address par son Root |
| SearchDiscovery / `SearchIndex` | `SearchIndexId` | `ListingId` source | `SearchDocumentId`; facettes sans identité globale | AggregateId + ListingId + SearchDocumentId à `add` | jamais tant que les historiques/projections existent ; retrait logique ne réattribue pas ces IDs | index et trois réservations annulés ensemble | permanente pour traçabilité et idempotence de reconstruction | SearchDiscovery ; ListingId et SearchDocumentId possédés par SearchIndex |

## 4. Portée des clés locales

Les clés suivantes sont des invariants du Root et non des réservations globales autonomes : ordre et checksum d’un MediaItem dans sa collection, rôle/consentement dans un Account, ListingRevisionId dans un Listing, MandateId dans un Professional, AddressId dans une Property et identités de décision/approbation internes à AdministrativeAction. Leur duplication doit être refusée par le Domain et leur état complet persiste avec le Root.

## 5. Idempotence

Seul `PaymentRegistry::registerIdempotently` déclare une idempotence métier sur une Business Key. La déduplication Lead est une exclusion temporelle, pas une réponse idempotente. Réutiliser une identité déjà possédée par le même Root lors d’une sauvegarde spécialisée est acceptable uniquement pour rendre la réservation stable ; cela ne doit jamais masquer une version périmée.

## 6. Critères d’acceptation futurs

Chaque test de contrat doit prouver : succès atomique, conflit par type de clé, propriété stable, absence totale après rollback, politique de réutilisation, conservation après état terminal et comportement concurrent avec un seul gagnant. Toute implémentation qui libère une réservation durable par purge, soft delete ou changement d’état est non conforme.
