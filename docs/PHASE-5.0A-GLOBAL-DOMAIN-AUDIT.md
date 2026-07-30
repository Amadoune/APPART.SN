# Phase 5.0A — Global Domain Audit

## 1. Décision et périmètre

Ce document consolide l'état réel du dépôt au 26 juillet 2026. Il prévaut,
pour la planification de la seconde moitié du projet, sur les roadmaps devenues
historiques. Il ne modifie aucun contrat, runtime, schéma, événement ou
comportement.

Le GO FINAL de la Phase 4.9 est pris comme décision d'autorité : **Account
Status Lifecycle est fermé et gelé**. Toute évolution d'une capacité gelée
exige un amendement versionné, une analyse d'impact et une recertification
ciblée.

Sources auditées :

- les 13 enveloppes sous `src/Modules`, leurs Aggregates, Value Objects,
  événements, ports, use cases et adapters PostgreSQL ;
- les controllers, bindings runtime, projections, read models et routes sous
  `app` et `routes` ;
- les migrations numérotées 001 à 043 ;
- les tests Architecture, Unit, Feature et PostgreSQL ;
- les ADR, blueprints, matrices, analyses et certifications des Phases 2, 3
  et 4 ;
- `MASTER-BLUEPRINT.md`, les documents d'architecture, `README.md`,
  `ROADMAP.md` et `CHANGELOG.md`.

Constat de gouvernance : `README.md` et la roadmap racine décrivent encore la
baseline Phase 2.8. Ils sont utiles comme historique, mais ne sont plus une
source fiable de l'état courant. Le code, les tests et les certifications
récentes font foi.

## 2. État consolidé

Le dépôt contient 12 modules métier implémentés, une enveloppe temporaire
`LegacyMigration`, 14 Aggregate Roots historiques et neuf chaînes de lifecycle
HTTP complètes. La projection publique dispose de sa source, de son store, de
son worker, de sa reconstruction, de sa réconciliation et de son endpoint de
lecture. L'Outbox partagé est une infrastructure de transport ; chaque domaine
producteur conserve cependant la propriété logique de ses lignes et de son
catalogue d'événements.

### 2.1 Capacités fermées et gelées

| Capacité | Owner | Chaîne certifiée | Statut |
|---|---|---|---|
| Listing Publication Lifecycle | ListingLifecycle | workflow à HTTP | gelée |
| Property Lifecycle | RealEstateCatalog | workflow à HTTP | gelée |
| Reservation Lifecycle | ReservationLifecycle | workflow à HTTP | gelée |
| Lead Lifecycle | ContactsLeads | workflow à HTTP, éligibilité incluse | gelée |
| Professional Status Lifecycle | Professionals | workflow à HTTP | gelée |
| Media Item Lifecycle | Media | workflow à HTTP | gelée |
| Administrative Action Lifecycle | AdministrationAudit | workflow à HTTP | gelée |
| Place Lifecycle | Geography | workflow à HTTP | gelée |
| Account Status Lifecycle | IdentityAccess | workflow, Historical Account, runtime, event, transport, routing, delivery, outbox, atomicité et HTTP | gelée par GO FINAL 4.9 |
| Public Listing Projection | couche de lecture publique | store, delivery, rebuild, reconciliation, sources et page publique | certifiée ; modification seulement par amendement |
| Historical Redirect et Canonical Qualification | ContentSeo | persistence, runtime et HTTP integration | certifiée ; gelée |
| Runtime Health et transport/outbox communs | plateforme | composition et contrôles | gelés comme dépendances des chaînes certifiées |

« Gelée » signifie que la capacité peut être consommée par un nouveau domaine,
mais ni étendue ni réinterprétée implicitement.

### 2.2 Capacités présentes mais non closes comme parcours produit

| Domaine | Présent dans le dépôt | Manque avant production |
|---|---|---|
| IdentityAccess | Account, rôles, consentements, vérifications, mot de passe | login/session, récupération, profil, fermeture/anonymisation, HTTP et politiques de sécurité hors statut |
| RealEstateCatalog | Property, repository, lifecycle | parcours de saisie/mise à jour, taxonomie, validation d'adresse et API de catalogue |
| ListingLifecycle | Listing, repository, publication lifecycle | dépôt guidé, édition, ownership annonceur, renouvellement UX et espace « mes annonces » |
| Media | MediaCollection, repository, ownership lookup, item lifecycle | upload binaire, stockage objet, validation MIME, variantes, scan, quotas et URLs signées |
| ContactsLeads | Lead aggregate et lifecycle | ingress téléphone/WhatsApp/message, consentement, anti-abus, déduplication opérationnelle, notifications |
| Professionals | Professional aggregate et status lifecycle | profil public, vérification, établissements, mandats, portefeuille et délégation |
| ReservationLifecycle | machine d'état et persistence | création, disponibilité, règles temporelles, ownership et expérience utilisateur |
| SearchDiscovery | SearchIndex et décisions PostgreSQL | API de recherche, filtres, tri, pagination, suggestions, SLA de fraîcheur |
| ContentSeo | SeoProjection, snapshots, redirects/canonical | CMS éditorial/légal, workflow de publication, sitemap opérationnel et administration SEO |
| AdministrationAudit | AdministrativeAction et lifecycle | console d'administration, lecture d'audit, habilitations et orchestration inter-domaines |

### 2.3 Capacités métier restantes exhaustives

1. **Moderation & Reports** : signalement, dossier, constat, décision,
   quatre-yeux, suspension/republication via commandes des owners, files et
   HTTP. L'Aggregate `ModerationCase` et ses use cases existent, mais aucune
   persistence ni chaîne runtime.
2. **Monetization & Payments** : catalogue produit administré immuable,
   commande, paiement, bénéfice, rapprochement, remboursement, webhooks et
   ledger. `Order` et `Payment` existent sans infrastructure. L'entrée en
   réalisation reste conditionnée à une décision commerciale.
3. **Account & Access Completion** : authentification, récupération,
   sessions, MFA éventuel, profil, fermeture, consentement et rôles. Toute
   mutation de `Account` ou `AccountRegistry` doit passer par amendement 4.9.
4. **Listing Authoring & Portfolio** : création/édition structurée, ownership,
   brouillons, soumission à la chaîne gelée et gestion annonceur.
5. **Property Catalog Completion** : taxonomie immobilière, caractéristiques,
   adresse, disponibilité métier et commandes de maintenance hors machine
   d'état.
6. **Media Ingestion & Processing** : upload, validation, stockage, variantes,
   scan, suppression physique différée et rattachement à `MediaCollection`.
7. **Professional Profile & Verification** : données publiques, établissements,
   mandats, vérification, portefeuille et habilitations d'équipe.
8. **Lead Ingress & Contact Channels** : création publique, politique de
   contactabilité, anti-spam, consentement, routage annonceur et rétention.
9. **Reservation Intake & Availability** : création, calendrier/disponibilité,
   conflit, expiration et ownership avant transition lifecycle.
10. **Search Experience** : query API, facettes, tri, pagination, pages
    intention/localité/type, observabilité de fraîcheur.
11. **Editorial Content & Operational SEO** : pages légales et guides,
    versioning éditorial, publication, sitemap et administration des
    redirections sans altérer la qualification historique gelée.
12. **Favorites** : nouvelle capacité absente du code ; owner IdentityAccess
    pour l'identité de la collection, références Listing en lecture seulement,
    confidentialité et suppression avec le compte.
13. **Notifications** : nouvelle capacité de delivery utilisateur ; préférences
    lues depuis IdentityAccess, consommation d'événements, aucun droit de
    mutation sur les domaines producteurs.
14. **Administration Console & Audit Read Model** : vues et commandes
    autorisées, séparation des rôles, journal consultable, aucune écriture
    directe dans les tables des autres domaines.
15. **Legacy Migration & Reconciliation** : qualification, import idempotent,
    rapprochement, cutover, redirects, preuves et extinction définitive de la
    source Legacy.
16. **Production Readiness** : sécurité, confidentialité/rétention,
    observabilité, sauvegarde/restauration, performance, accessibilité,
    frontend, runbooks, déploiement, répétition de migration et lancement.

## 3. Frontières de lecture et d'écriture

Règle absolue : un domaine écrit uniquement son Aggregate, ses snapshots,
son inbox et ses lignes d'Outbox. Il consomme les autres domaines par port de
lecture, projection ou événement versionné. Une transaction ne traverse pas
deux owners métier ; la coordination inter-domaines est asynchrone ou passe
par un use case du domaine cible.

| Owner | Écrit | Peut lire | Ne peut jamais écrire directement |
|---|---|---|---|
| IdentityAccess | comptes, credentials, rôles, consentements | audit et références de contexte minimales | professionnels, listings, leads |
| Professionals | professionnels, établissements, mandats | Account status, catalogue Listings | Account, Listing |
| RealEstateCatalog | properties, adresse, caractéristiques | Geography | Place, Listing |
| ListingLifecycle | listings, révisions, publication | Property, Media, Professional/Account status | Property, Media, Account |
| Media | collections, items, metadata technique | ownership Property/Listing | Listing, Property |
| Geography | places, aliases, merges | aucun owner métier requis | projections consommatrices |
| ContactsLeads | leads, consent/eligibility evidence | Listing contactability, advertiser status | Listing, Professional, Account |
| ReservationLifecycle | réservations et transitions | Property/Listing availability, account status | Property, Listing, Account |
| ModerationReports | cases, reports, findings, decisions | Listing, Media, Account/Professional status | états Listing/Media/Account ; commande obligatoire |
| MonetizationPayments | products, orders, payments, benefits | account/professional, listing cible | Listing rank/status, Account |
| SearchDiscovery | index/projection reconstruisible | projection publique certifiée | sources métier |
| ContentSeo | contenu, SEO projection et décisions propres | projection publique, Geography | Listing/Property/Place |
| Favorites | collections de favoris | Listing public ID/status | Listing |
| Notifications | préférences de canal propres, deliveries | événements et préférences autorisées | tout Aggregate producteur |
| AdministrationAudit | actions, approvals et audit | identités et résultats de commandes | tables métier des autres owners |
| LegacyMigration | checkpoints, preuves, rapprochement | sources Legacy et ports d'import | modèles courants hors ports d'import dédiés |

## 4. Risques structurants

| Risque | Sévérité | Traitement obligatoire |
|---|---|---|
| Confondre lifecycle certifié et parcours produit complet | critique | phase de completion séparée, aucune extension silencieuse |
| Ajouter de nouveaux types dans l'Outbox partagé | critique | owner logique unique, amendement versionné si contrat commun touché |
| Modération écrivant directement Listing/Account | critique | commandes explicites vers owners et saga auditée |
| Monétisation influençant le classement organique | élevé | politique commerciale et transparence avant code |
| Favorites/Notifications absorbés par un domaine existant | élevé | bounded contexts légers mais propriétaires |
| Mutation de Account après 4.9 | critique | amendement 4.9 préalable |
| Projection utilisée comme source d'écriture | critique | projections strictement reconstruisibles |
| Migration Legacy devenant runtime permanent | critique | critères d'extinction et date de cutover |
| Provider runtime monolithique | élevé | ne pas le modifier dans 5.0A ; planifier composition additive et recertification |
| Documentation racine obsolète | moyen | réalignement documentaire en Phase 5.0B |

## 5. Gates proposés

La Phase 5.0A peut être proposée **GO** : tous les domaines restants ont un
owner, une frontière, des dépendances et une place ordonnée dans la roadmap.

Ce GO n'autorise aucune implémentation. Chaque phase future doit commencer par
un Discovery/Blueprint et satisfaire :

1. owner unique ;
2. matrice read/write ;
3. événements et consommateurs versionnés ;
4. décision explicite sur l'impact des capacités gelées ;
5. persistence et rollback propres ;
6. tests contractuels, PostgreSQL, concurrence et architecture ;
7. certification puis gel.
