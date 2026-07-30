# Phase 4.4 — Capability Discovery

## Recommandation

La Phase 4.4 doit porter **Lead Lifecycle**, propriété du module `ContactsLeads`.

Le code existant fournit des preuves particulièrement nettes :

- Aggregate Root `Lead` avec version, historique et événements dans `src/Modules/ContactsLeads/Domain/Model/Lead.php` ;
- quatre états fermés dans `LeadStatus` : `Created`, `Delivered`, `Rejected`, `Closed` ;
- commandes existantes `CreateLead`, `DeliverLead`, `RejectLead`, `CloseLead` ;
- faits existants `LeadCreated`, `LeadDelivered`, `LeadRejected`, `LeadClosed` ;
- port propriétaire `LeadRegistry` et ports de preuve `ListingCatalog`, `AdvertiserCatalog` ;
- contrôle de concurrence et déduplication déjà exprimés contractuellement par `LeadRegistry`.

## Frontière métier retenue

Lead Lifecycle gère la réception d'une intention de contact consentie, sa remise à l'annonceur ou son rejet, puis sa clôture. Il ne gère ni la publication du Listing, ni l'état du Property, ni la réservation. Les identités Listing et Advertiser sont des références externes accompagnées de preuves figées au moment de la création.

Cycle observé :

```text
Created → Delivered → Closed
Created → Rejected  → Closed
```

La création est une entrée explicite du cycle, soumise au consentement, à la contactabilité du Listing, à l'éligibilité de l'annonceur et à la déduplication.

## Maturité

Le domaine est mature mais son infrastructure est absente : 53 fichiers PHP, deux modèles, quatre événements, quatre use cases, trois ports, aucune implémentation PostgreSQL. Ce profil est idéal pour une progression Phase 4 : le comportement est concret, tandis que chaque fondation technique peut être certifiée sans héritage infrastructurel ambigu.

## Candidats non retenus

- **Payment Lifecycle** : domaine riche mais composé d'Order, Payment, Benefit et Refund, avec risque financier et frontières transactionnelles multiples.
- **Account Lifecycle** : très mature, mais mélange statut de compte, credentials, vérifications, rôles et consentements ; la décomposition contractuelle doit précéder un vertical.
- **Moderation Lifecycle** : cycle clair, mais décision et effets sur Listing doivent être séparés pour éviter une dépendance circulaire avec Listing Publication.
- **Professional Lifecycle** : pertinent, mais mandats et établissements élargissent le premier périmètre.
- **Administrative Action** : persistance déjà disponible, mais capacité support plutôt qu'un prochain flux produit prioritaire.
- **Offer, Application, Visit, Contract, Messaging** : aucune capacité autonome correspondante n'existe dans les modules. Les occurrences de « visitor » concernent l'identité d'un contact, pas un Visit Lifecycle.
