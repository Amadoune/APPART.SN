# APPART.TEST LOCAL PUBLIC PROJECTION POPULATION 01 — Pipeline Audit

## Verdict de frontière

`NO GO PROPOSÉ`

Le repository contient les briques certifiées de lecture, de reconstruction et de publication, mais aucun point d'entrée local exécutable ne permet de produire de bout en bout une annonce publique sans contourner une frontière interdite par le jalon.

## État observé

| Élément | Preuve observée | État |
|---|---|---|
| PostgreSQL local | PostgreSQL 18.4, base `appart_test`, connexion `pgsql` | PASS |
| Property | `real_estate_catalog.properties` : 0 ligne | MISSING |
| Listing | `listing_lifecycle.listings` : 0 ligne | MISSING |
| Décision Search publique | `search_discovery.public_search_decisions` : 0 ligne | MISSING |
| Snapshot Content/SEO | `content_seo.public_source_snapshots` : 0 ligne | MISSING |
| Géographie publique | `public_geography.decisions` : 0 ligne | MISSING |
| Média public | `public_media.decisions` : 0 ligne | MISSING |
| Génération publique | `public_projection.generations` : 0 ligne | MISSING |
| Projection publique | `public_projection.listing_projections` : 0 ligne | MISSING |
| Endpoint collection | `GET /api/public-search/results` → HTTP 200, `status=empty` | PASS technique / MISSING donnée |

## Matrice du pipeline requis

| Étape | Contrat / chemin certifié identifié | Writer / store | Preuve attendue | Exécutabilité locale conforme |
|---|---|---|---|---|
| Property | `PropertyRegistry` | repository PostgreSQL certifié | Property persisté | partielle : aucun bootstrap local orchestré |
| Listing | création Listing et `ListingRegistry` | repository PostgreSQL certifié | Listing draft persisté | partielle : contrats présents |
| Authoring | `PropertyListingAuthoringOperations` | stores authoring certifiés | données d'authoring complètes | partielle : pas de scénario local complet |
| Publication | `ListingPublicationOrchestrator` | `ListingPublicationWorkflowStore` | transitions Submit, BeginReview, ApproveAndPublish | partielle : workflow distinct de la projection agrégée |
| Search | décision publique Search | `PostgreSqlSearchDecisionWriter` | décision éligible | writer présent, aucune commande locale |
| Content/SEO | snapshot public Content/SEO | `PostgreSqlContentSeoSourceSnapshotWriter` | canonical courant | writer présent, aucune commande locale |
| Geography | décision publique | `PostgreSqlPublicGeographyWriter` | localisation publique | writer présent, aucune commande locale |
| Media | décision publique | `PostgreSqlPublicMediaWriter` | média public | writer présent, aucune commande locale |
| Génération candidate | `PublicProjectionGenerationManager::createCandidate` | store génération | génération candidate | contrat présent |
| Reconstruction | `PublicProjectionRebuilder` / worker certifié | writer de projection | record candidat | contrat présent |
| Activation | `PublicProjectionGenerationManager::activate` | store génération | génération active validée | contrat présent |
| Lecture | `PublicSearchResultsReaderV1` | projection publique | `available` + item | opérationnel mais source vide |

## Cause racine

Le chaînage des briques n'est exposé par aucune commande Artisan, aucun script de développement certifié et aucun orchestrateur local unique. Le test `PublicProjectionEndToEndRuntimeCertificationTest` n'est pas une procédure de population recevable : il insère directement une génération active dans `public_projection.generations` et fabrique un Listing publié par appels directs aux transitions de l'Aggregate avant écriture au Registry.

Réutiliser ce test ou en copier la fixture violerait respectivement l'interdiction de SQL direct dans les tables de génération et l'interdiction de bypass de publication/modération.

## Écart minimal à traiter ultérieurement

Une décision séparée devrait autoriser une commande de développement locale, non disponible en production, orchestrant exclusivement les contrats existants : création minimale, transitions de publication/modération, décisions publiques, génération candidate, reconstruction, validation et activation, avec une opération de nettoyage symétrique. Aucun de ces éléments n'est créé dans ce jalon.
