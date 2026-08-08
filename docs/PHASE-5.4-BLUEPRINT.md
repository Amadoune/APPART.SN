# Phase 5.4 — Discovery / Blueprint

## Statut

`DISCOVERY / BLUEPRINT — OUVERT — DOCUMENTAIRE UNIQUEMENT`

Ce document décrit la gouvernance candidate de la Phase 5.4. Il ne crée
aucun contrat exécutable, aucune autorisation d'implémentation et aucune
ouverture de Foundation.

## 1. Besoin métier

La Phase 5.4 complète trois parcours utilisateur laissés hors des lifecycles
historiques :

1. **5.4A — Lead Ingress & Contact Delivery** : permettre à un visiteur de
   contacter l'annonceur d'un Listing public, avec consentement, anti-abus,
   confidentialité et livraison traçable ;
2. **5.4B — Reservation Intake & Availability** : recevoir une demande de
   réservation, vérifier une disponibilité propriétaire et prévenir les
   conflits avant son entrée dans le lifecycle Reservation existant ;
3. **5.4C — Favorites** : fournir à un Account authentifié une collection
   privée et propriétaire de Listings favoris.

Ces trois voies sont indépendantes. Leur parallélisation est autorisable
uniquement après certification de leurs gates et frontières respectives.

## 2. Objectifs fonctionnels

### 2.1 Lead Ingress & Contact Delivery

- accepter une intention de contact sur un Listing publiquement contactable ;
- conserver une preuve minimale du consentement et du contexte de soumission ;
- appliquer une politique anti-abus déterministe et fail-closed ;
- résoudre le destinataire professionnel par une frontière publique certifiée ;
- livrer sur un canal autorisé sans exposer les coordonnées du destinataire ;
- assurer idempotence, retry borné, replay sûr, quarantaine et rétention ;
- fournir au demandeur un accusé non énumérant et à l'annonceur une lecture
  privée autorisée.

### 2.2 Reservation Intake & Availability

- créer une intention de réservation avec identité et ownership explicites ;
- vérifier Listing, Property et fenêtre de disponibilité par frontières
  publiques ;
- détecter les conflits de concurrence de manière déterministe ;
- transmettre une demande valide au lifecycle Reservation sans le contourner ;
- garantir expiration, idempotence et absence de double réservation ;
- fournir des lectures privées strictement auto-scopées.

### 2.3 Favorites

- créer implicitement ou explicitement une collection privée par Account ;
- ajouter, retirer, vérifier et lister des références de Listings publics ;
- préserver un ordre et une pagination déterministes ;
- ne jamais transformer la collection en autorité sur le Listing ;
- traiter l'indisponibilité ou la suppression d'un Listing sans mutation
  cross-domain ;
- définir la rétention et la suppression liées au lifecycle de l'Account.

## 3. Frontières de domaine et ownership

| Capacité | Owner candidat | Autorité possédée | Autorités non possédées |
|---|---|---|---|
| Lead ingress | `ContactsLeads` | Lead, intention de contact, preuve locale de consentement, delivery propriétaire | Listing, Professional, Account, coordonnées professionnelles |
| Reservation intake | `ReservationLifecycle` | demande de réservation et transitions de réservation | Listing, Property, calendrier d'un autre owner, Account |
| Favorites | `Favorites` | `FavoriteCollection` et ses entrées | Account, Listing, projection publique |

La matrice d'ownership historique attribue `FavoriteCollection` à `Favorites`.
Une formulation antérieure attribue toutefois « l'identité de la collection »
à `IdentityAccess`. Cette divergence doit être levée par une décision
documentaire avant Contracts Foundation. À défaut, la voie 5.4C est NO GO.

### Consumers identifiés

- visiteur public ou Account authentifié pour Lead Ingress ;
- annonceur ou représentant mandaté pour les leads reçus ;
- Account authentifié pour Reservation Intake et Favorites ;
- owners Listing, Property et Professional uniquement derrière des frontières
  publiques certifiées ;
- workers owner-scoped de delivery, retry et replay ;
- AdministrationAudit uniquement si un append public certifié est requis par
  une gate ultérieure.

## 4. Modèle conceptuel candidat

Les éléments ci-dessous sont des objets de discussion. Ils ne constituent ni
des contrats, ni des classes, ni un catalogue certifié.

| Voie | Aggregate Root | Entités candidates | Value Objects candidats |
|---|---|---|---|
| 5.4A | `Lead` existant | ContactIntent, ConsentEvidence, DeliveryAttempt | LeadId, ListingId, Channel, IntentId, Checksum, RetentionDeadline |
| 5.4B | `Reservation` existant | ReservationRequest, AvailabilityObservation, ConflictEvidence | ReservationId, ListingId, PropertyId, StayWindow, ExpectedVersion, IntentId |
| 5.4C | `FavoriteCollection` nouveau | FavoriteEntry | CollectionId, AccountId, ListingId, AddedAt, Revision, Cursor |

### Invariants conceptuels

- un owner unique décide chaque mutation ;
- les identifiants cross-domain restent opaques ;
- aucune FK, cascade ou transaction ACID cross-domain ;
- même intent et même checksum convergent vers un résultat déjà appliqué ;
- même intent et checksum différent produisent une divergence fail-closed ;
- les lectures externes sont versionnées, fermées et fail-closed ;
- aucun payload public ou événementiel ne contient secret, diagnostic interne
  ou donnée personnelle non indispensable ;
- les projections et caches ne deviennent jamais des autorités d'écriture.

## 5. Commands, Queries et Events candidats

### 5.4A

- Commands candidats : soumettre un contact, enregistrer une décision
  d'éligibilité, demander une livraison, reprendre ou quarantiner une livraison ;
- Queries candidates : lire son accusé, lire une boîte de leads autorisée, lire
  le statut d'une livraison ;
- Events candidats : ingress accepté ou rejeté, livraison demandée, livrée,
  échouée ou quarantinée.

### 5.4B

- Commands candidats : soumettre une demande, réserver une fenêtre, expirer,
  transmettre au lifecycle, annuler une intention ;
- Queries candidates : lire sa demande, lire sa disponibilité autorisée, lire
  les conflits sans exposer d'autres réservations ;
- Events candidats : demande soumise, disponibilité confirmée, conflit détecté,
  demande expirée, handoff lifecycle effectué.

### 5.4C

- Commands candidats : ajouter, retirer et vider les favoris ;
- Queries candidates : lister la collection et vérifier une appartenance ;
- Events candidats : favori ajouté, retiré, collection vidée.

Aucun nom ci-dessus n'est un Event V1 ou un contrat normatif. Les catalogues
fermés, payloads, résultats et identités seront décidés uniquement dans une
Contracts Foundation explicitement ouverte.

## 6. Frontières publiques candidates

| Frontière | Owner attendu | Usage |
|---|---|---|
| Listing public contactability read | Listing/Public Projection | autoriser ou refuser un contact sans lire Aggregate ou SQL |
| Advertiser delivery resolution | Professional Core/Profile | résoudre un destinataire autorisé sans exposer mandat ou coordonnées |
| Consent/abuse decision | owner à qualifier | produire une décision minimale, versionnée et fail-closed |
| Listing/Property availability read | Listing/Property | vérifier une fenêtre sans lire calendrier ou persistence interne |
| Reservation lifecycle handoff | ReservationLifecycle | faire entrer une demande valide dans le lifecycle certifié |
| Account session/availability | IdentityAccess | auto-scope et disponibilité, sans assimiler Account aux autres IDs |
| Public Listing eligibility for Favorites | Listing/Public Projection | valider une référence sans obtenir le lifecycle interne |
| Account closure notification/command | IdentityAccess | déclencher la politique de suppression sans mutation directe |

La présence d'un composant interne ou d'une classe existante ne vaut pas
certification d'une frontière publique. Chaque ligne doit être auditée avant
consommation.

## 7. Frontières internes

- Stores, mappers, transactions et diagnostics restent owner-scoped ;
- les adaptateurs HTTP ne consomment que les Commands et Queries certifiés ;
- les workers ne routent que des destinations fermées ;
- les transactions englobantes restent limitées à un owner ;
- retry, replay, claim et quarantaine n'interprètent aucune règle métier ;
- les diagnostics internes ne sont jamais propagés dans une réponse publique.

## 8. Capacités réutilisables sous réserve d'audit

- lifecycles `Lead` et `Reservation` existants ;
- projection publique Listing et ses contrats publics certifiés ;
- session et availability IAM certifiées ;
- frontières publiques Professional Status et Professional Mandate ;
- mécanismes génériques owner-local de transaction, outbox, delivery et
  observabilité, seulement si leur ownership et leur extension sont autorisés.

La réutilisation signifie consommation d'un contrat public certifié, jamais
accès à un Aggregate, store, Repository, SQL, Runtime interne ou migration.

## 9. Hypothèses et exclusions

### Hypothèses à certifier

- un Listing public peut exposer une décision de contactabilité minimale ;
- un mandat professionnel peut résoudre un destinataire de contact autorisé ;
- l'owner Property/Listing peut exposer une décision de disponibilité ;
- la session IAM fournit un AccountId auto-scopé ;
- la suppression d'un Account dispose d'un mécanisme public compatible avec
  la suppression des Favorites.

### Hors périmètre

- messagerie temps réel, chat et pièces jointes ;
- paiement, dépôt de garantie et facturation ;
- moteur de calendrier généraliste ou synchronisation iCal ;
- scoring commercial, ranking ou recommandations ;
- notifications génériques de la Phase 5.5C ;
- moteur antifraude global ;
- CRM externe et export de données ;
- partage public de favoris ;
- toute modification de la Phase 5.3 gelée.

## 10. Décision de Discovery proposée

La Phase 5.4 est faisable comme trois voies owner-scoped, sous réserve de
certifier les frontières listées et de lever l'ambiguïté Favorites/IAM.
Le Discovery n'autorise aucune Foundation suivante.

