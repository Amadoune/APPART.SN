# Changelog

## [A-5.3-BASELINE-IMPORT-WHITESPACE-NORMALIZATION-01] — 2026-07-31

- Normalisation strictement limitée aux 239 chemins certifiés : 220 Markdown
  et 19 SQL historiques gelés.
- Retrait mécanique d'un unique LF terminal par fichier, soit 239 octets.
- Architecture complète : 697 tests, 55 965 assertions — PASS.
- PostgreSQL complet : 680 tests, 3 070 assertions — PASS.
- `git diff --cached --check` et `git diff --check` : PASS.
- Aucun changement lexical, syntaxique ou sémantique SQL.
- Statut : GO PROPOSÉ.

## [A-5.3-BASELINE-IMPORT-WHITESPACE-QUALIFICATION-01] — 2026-07-31

- Amendement de gouvernance ouvert pour qualifier 239 erreurs
  `new blank line at EOF` lors de la première matérialisation Git.
- Périmètre confirmé : 220 Markdown et 19 SQL historiques gelés.
- Transformation candidate limitée au retrait d'un unique LF terminal par
  fichier, sans changement lexical, syntaxique ou sémantique SQL.
- Option recommandée : normalisation bornée avec Architecture et PostgreSQL
  complets, sans exception Git.
- Statut : GO CERTIFIÉ — FERMÉ.

## [A-5.3-PINT-GLOBAL-GATE-QUALIFICATION-01] — 2026-07-30

### Qualification documentaire

- Pint global confirmé rouge sur le seul serializer de transport 5.3G gelé.
- Trois fixers de formatage identifiés, sans défaut fonctionnel Report.
- Baseline qualité : Pint global obligatoirement vert.
- Dérogation ad hoc jugée non recevable sous les règles existantes.
- Amendement versionné 5.3G recommandé ; aucun correctif ni ouverture implicite.

### Décision et correction certifiée

- Qualification approuvée.
- Amendement 5.3G de formatage GO CERTIFIÉ, FERMÉ et GELÉ.
- Pint ciblé et Pint global PASS, exit code 0.
- Gate Pint globale officiellement levée.
- Aucune modification sémantique.

## [A-5.3-POSTGRESQL-FULL-CAMPAIGN-DIAGNOSTIC-01] — 2026-07-30

### Diagnostic technique

- Campagne complète démarrée avec PostgreSQL 18.x, PHPUnit 12.5.31 et 658 tests.
- Progression continue et tests PASS observés pendant 600 secondes.
- Interruption par la limite externe avant résultat terminal.
- Aucun deadlock, test Report fautif ou erreur de migration identifié.
- Coût dominant : réapplication séquentielle des 69 migrations et resets
  globaux dans les fixtures.
- Aucun correctif, nettoyage de processus ou modification technique effectué.
- Rejeu dédié avec fenêtre supérieure à 600 secondes recommandé ; aucun PASS
  global revendiqué.

### Décision d'autorité

- Campagne complète terminale : 658 tests, 2 935 assertions, zéro failure,
  zéro erreur, exit code 0.
- Durée complète : 743,801 secondes.
- Réserve PostgreSQL officiellement levée.
- Diagnostic PostgreSQL approuvé et clôturé.
- À cette étape historique, la fondation Report restait IMPLÉMENTATION CONFORME
  — GO NON PROPOSABLE ; cette réserve Pint est désormais levée par l'amendement
  5.3G certifié consigné plus haut.

## [A-5.3-GLOBAL-PROOF-QUALIFICATION] — 2026-07-30

### Audit documentaire

- PostgreSQL ciblé confirme la fondation Report, mais ne remplace pas la
  campagne complète obligatoire.
- Les timeouts complets sont NON CONCLUSIFS et bloquent le GO sans invalider
  fonctionnellement Report.
- L'écart Pint est localisé en 5.3G gelé, hors périmètre Report.
- Une réserve attribue l'écart mais ne neutralise pas la gate Pint obligatoire.
- Qualification bloquante proposée ; aucun correctif ni nouvelle campagne
  exécutés.

## [A-5.3-MODERATION-REPORT-OWNER-READ-SOURCE-01] — 2026-07-30

### Discovery

- Audit strictement documentaire de la source owner Report.
- `reportId` persisté dans `moderation_reports.report_revisions`.
- `caseId` déjà dérivé par
  `deterministicUuid('moderation-case-v1', reportId)`.
- Résolution réalisable par clé primaire Case puis index composite des révisions.
- Aucun scan, index ou migration additive nécessaire.
- Discovery GO CERTIFIÉ et FERMÉ ; aucun port, reader, Query, binding, Runtime,
  HTTP ou test créé pendant ce jalon documentaire.
- Source Queue maintenue IDENTIFIÉE, NON OUVERTE.

### Contracts & Implementation Foundation

- Port read-only, résultat fermé et état minimal implémentés.
- Adapter PostgreSQL owner-scoped sans migration ni scan.
- Identité canonique réutilisée via un port interne, sans duplication.
- Binding singleton/lazy/unique et tests ciblés verts.
- Architecture et Unit complètes vertes ; PHPStan et Pint ciblé verts.
- Verdict d'autorité : GO CERTIFIÉ — FERMÉ — GELÉ.
- Preuve PostgreSQL complète terminale reconnue : 658 tests, 2 935 assertions,
  zéro failure, zéro erreur, exit code 0.
- Réserve PostgreSQL levée ; la certification avait alors été suspendue
  uniquement par la gate Pint globale relative à l'écart historique 5.3G.
- Gate Pint globale désormais levée par amendement 5.3G certifié.
- Aucune réserve globale restante.
- Toute évolution future de la source owner Report exige un amendement
  versionné.
- Aucun jalon Queue, HTTP, 5.3K ou 5.3L ouvert par ce verdict.
- Amendement désormais GO CERTIFIÉ, FERMÉ et GELÉ ; aucune autre fondation
  ouverte par ce verdict.

## [A-5.3-MODERATION-HTTP-READ-BOUNDARIES-01] — 2026-07-30

### Boundary Audit

- Amendement NO GO CERTIFIÉ et FERMÉ après le NO GO du Discovery 5.3J.
- Contrats, résultats, matrice et stratégie Runtime des quatre Queries définis.
- Sources dossier et décision disponibles.
- Résolution owner par reportId et lecture filtrée/paginée de queue absentes.
- Aucun port PHP, binding, reader partiel, migration ou HTTP créé.
- Deux sources owner additives distinctes identifiées, NON OUVERTES.
- La source Report est la première ouverture recommandée ; la Queue demeure
  NON OUVERTE.


## [Phase 5.3J — HTTP & Security Discovery] — 2026-07-30

### Audit

- Phase 5.3J explicitement ouverte au Discovery uniquement.
- Session IAM, autorisation modérateur et six Commands V1 exécutables confirmés.
- Quatre Queries V1 présentes uniquement dans la documentation 5.3B : aucun
  port PHP, reader owner-scoped, résultat typé ou binding Laravel.
- Accès direct aux stores, états de persistence ou SQL formellement écarté.
- NO GO CERTIFIÉ et Discovery FERMÉ.
- Gate préalable recommandé :
  `A-5.3-MODERATION-HTTP-READ-BOUNDARIES-01`.
- Aucun Controller, Request, Route, Middleware, provider, test ou code HTTP créé.
- 5.3K et 5.3L restent NON OUVERTS.

## [A-5.3-ROADMAP-SEQUENCING-ALIGNMENT-01] — 2026-07-30

### Gouvernance documentaire

- Amendement GO CERTIFIÉ, FERMÉ et intégré à la gouvernance normative.
- 5.3I reste Command Handoff Integration / Listing, GO CERTIFIÉ et FERMÉ.
- La séquence prospective devient 5.3J HTTP & Security, 5.3K Operational &
  Audit Certification, puis 5.3L Final Certification & Freeze.
- La séquence historique du Blueprint est conservée et annotée comme remplacée
  prospectivement.
- Aucun jalon suivant n'est ouvert implicitement.
- 5.3J HTTP & Security reste NON OUVERT et seulement autorisable par décision
  d'autorité explicite distincte.
- Aucun code, contrat, Runtime, HTTP, Event, Delivery, Outbox ou schéma n'est
  modifié.

## [Phase 5.2C — Professional Mandate Owner Source Foundation] — 2026-07-28

### Foundation

- Source owner canonique Account → Professional créée.
- Migration additive 062 et rollback.
- Résolution déterministe zéro/une/plusieurs cibles.
- Updater versionné, idempotent et transactionnel.
- Aucun resolver, provider, binding, Runtime ou HTTP.
- GO probatoire en attente de PostgreSQL terminal.

## [Phase 5.2C — Owner Read Implementations & Runtime Bindings] — 2026-07-28

### Representation

- Reader F-05 techniquement faisable sur sa source owner existante.
- Resolver de mandat impossible sans source owner Account/mandat/Professional.
- Aucun registre concret, persistence de mandat ou reverse index disponible.
- NO GO CERTIFIÉ, FERMÉ sans code partiel, fallback ou binding non résolvable.

## [Phase 5.2C — HTTP Foundation corrective] — 2026-07-28

### Representation

- Les deux frontières publiques sont GO CERTIFIÉES et FERMÉES.
- Compatibilité HTTP spécifiée exclusivement via ces contrats.
- NO GO proposé : aucune implémentation owner ni binding n’existe et leur
  création reste hors périmètre.
- Aucun composant HTTP artificiellement non résolvable n’est créé.

## [A-5.2C-PROFESSIONAL-MANDATE-PUBLIC-RESOLUTION-01] — 2026-07-28

### Boundary Amendment

- Ajout de `ProfessionalMandateResolverV1`.
- Résolution V1 fermée et fail-closed.
- `ProfessionalId` exposé uniquement pour `Resolved`.
- Aucun import IAM, Aggregate, mandat, établissement ou détail de persistence.
- Aucun adapter, binding, Runtime ou HTTP créé.
- GO CERTIFIÉ et FERMÉ.

## [A-5.2C-PROFESSIONAL-MANDATE-RESOLUTION-01] — 2026-07-28

### Audit

- Audit documentaire de la chaîne Account → Representative Mandate →
  Professional.
- Aucun contrat public ou mapping typé AccountId/RepresentativeId existant.
- Registre, commandes, événements et IAM exclus comme frontières de query.
- NO GO proposé ; extension publique owner Professional Core requise.

## [A-5.2C-PROFESSIONAL-STATUS-PUBLIC-READ-01] — 2026-07-28

### Boundary Amendment

- Ajout du contrat public owner F-05 `ProfessionalPublicStatusReaderV1`.
- Catalogue V1 fermé, read-only et fail-closed.
- Aucun snapshot, version, persistence ou détail Runtime exposé.
- Aucun adapter, binding, SQL, HTTP, Event, Delivery ou Outbox créé.
- GO CERTIFIÉ et FERMÉ.

## [A-5.2C-PROFESSIONAL-STATUS-READ-BOUNDARY-01] — 2026-07-28

### Audit

- Audit documentaire ouvert sans modification technique.
- Aucun contrat public F-05 de décision de disponibilité n’existe.
- Store, orchestrateur, HTTP de transition et événements exclus comme frontières
  de query cross-domain.
- NO GO proposé ; amendement versionné d’extension publique F-05 requis.

## [Phase 5.2C — HTTP Foundation] — 2026-07-28

### Governance

- Runtime Foundation GO CERTIFIÉE et FERMÉE.
- HTTP Foundation NO GO CERTIFIÉ et maintenue OUVERTE.
- Audit préalable : résolution Account/mandat → ProfessionalId absente et
  frontière F-05 toujours non ouverte.
- Aucun amendement ni jalon suivant ouvert.

## [Phase 5.2C — Runtime Foundation] — 2026-07-28

### Governance

- Persistence Foundation GO CERTIFIÉE et FERMÉE ; migration 061 certifiée.
- Runtime Foundation ouverte comme seul jalon autorisé.
- Composition strictement limitée aux trois persistences 5.2C.
- Réserve `A-5.2C-PROFESSIONAL-STATUS-READ-BOUNDARY-01` non ouverte et aucune
  lecture F-05.

## [Phase 5.2C — Persistence Foundation] — 2026-07-28

### Governance

- Contracts Foundation GO CERTIFIÉE et FERMÉE.
- Persistence Foundation ouverte comme seul jalon autorisé.
- Première migration additive autorisée après la baseline gelée 060.
- Aucun Runtime, HTTP, Event, Delivery, Outbox, provider, route ou consumer.

## [Phase 5.2C — Contracts Foundation] — 2026-07-28

### Governance

- Discovery / Blueprint GO CERTIFIÉ et FERMÉ.
- Contracts Foundation ouverte comme seul jalon autorisé.
- Réserve `A-5.2C-PROFESSIONAL-STATUS-READ-BOUNDARY-01` maintenue identifiée,
  non ouverte et bloquante avant Runtime/HTTP.
- Aucun code, interface, port, migration, Event, Runtime, HTTP, Outbox, provider,
  route ou test.

## [Phase 5.2B GO FINAL / Phase 5.2C Discovery] — 2026-07-28

### Governance

- Phase 5.2B GO FINAL CERTIFIÉE, FERMÉE et GELÉE.
- Activation de F-21 Media Ingestion et F-22 Migrations 058–060.
- Réserve Reservation Lifecycle maintenue hors périmètre.
- Ouverture exclusive du Discovery / Blueprint Professional Profile 5.2C.
- Aucune implémentation, migration, interface, Runtime, HTTP, Event ou test.

## [Phase 5.2B — Final Certification & Freeze] — 2026-07-28

### Governance

- Ouverture du seul jalon documentaire Final Certification & Freeze.
- Consolidation des six Foundations GO CERTIFIÉES et FERMÉES.
- Migrations 058–060 certifiées, additives, inchangées et gelées.
- Réserve Reservation Lifecycle maintenue hors périmètre et sans modification.
- Aucun amendement ouvert et aucune ouverture de 5.2C.

## [Phase 5.2B — Atomic Delivery / Outbox Integration] — 2026-07-28

### Added

- Outbox propriétaire Media Ingestion et migration additive 060.
- Writer/reader owner-scoped avec convergence `message_id`/`event_id`.
- Claims concurrents `FOR UPDATE SKIP LOCKED`, lease, retry et quarantaine.
- Composition atomique avec participation sûre par savepoint.
- Provider lazy/singleton distinct.

### Validation et décision

- ciblés hors PostgreSQL : 3 tests, 24 assertions, PASS ;
- Architecture complète : 636 tests, 49 668 assertions, PASS ;
- Unit complète : 1 930 tests, 6 806 assertions, PASS ;
- PHPStan : 0 erreur ; Pint : PASS ;
- PostgreSQL ciblé : 4 tests, 24 assertions, PASS ;
- migration 060 corrigée et certifiée avec `message_id varchar(83)` ;
- GO CERTIFIÉ, FERMÉE ;
- campagne PostgreSQL globale non verte uniquement en raison de l’ambiguïté
  historique Reservation Lifecycle, classée hors périmètre et non-régression
  Media Ingestion ;
- aucun jalon suivant ouvert.

## [Phase 5.2B — Event / Transport / Routing / Delivery] — 2026-07-28

### Added

- Catalogue fermé à trois Events Media Ingestion V1.
- Transport canonique avec détection d’altération.
- Routing multi-destination fermé.
- Consumer validant transport et destination.
- Retry borné, replay, quarantaine et observation technique sans PII.

### Validation

- ciblés : 10 tests, 196 assertions, PASS ;
- Architecture complète : 635 tests, 49 485 assertions, PASS ;
- Unit complète : 1 929 tests, 6 805 assertions, PASS ;
- PHPStan 0 erreur et Pint PASS ;
- PostgreSQL ciblé : 1 test, 5 assertions, PASS ;
- PostgreSQL complet : 602 tests, 2 618 assertions, PASS ;
- GO CERTIFIÉ, FERMÉE.

## [Phase 5.2B — Runtime Foundation] — 2026-07-28

### Added

- Cinq providers Runtime owner-scoped.
- Façade publique `MediaIngestionRuntimeV1`.
- Availability fail-closed et diagnostics fermés.
- Bindings lazy/singleton partageant la connexion Runtime.
- Tests Unit, Feature, Architecture et PostgreSQL Runtime.

### Validation

- ciblés : 7 tests, 113 assertions, PASS ;
- Architecture complète : 632 tests, 49 013 assertions, PASS ;
- Unit complète : 1 923 tests, 6 788 assertions, PASS ;
- PHPStan 0 erreur et Pint PASS ;
- PostgreSQL Runtime : 1 test, 5 assertions, PASS ;
- PostgreSQL complet : 601 tests, 2 613 assertions, PASS ;
- Runtime Foundation GO CERTIFIÉE et FERMÉE.

## [Phase 5.2B — Persistence Foundation] — 2026-07-28

### Added

- Quatre states, ports, mappers et stores owner-scoped Media Ingestion.
- Historique permanent des intents et optimistic locking.
- Contrat PHP `AttachReadyMediaAssetV1` et journal Attachment.
- Migrations additives 058 et 059.
- Tests Unit, Architecture et PostgreSQL ciblés.

### Status

- Persistence Foundation GO CERTIFIÉE et FERMÉE.
- Unit/Architecture ciblés : 5 tests, 91 assertions, PASS.
- Architecture complète : 629 tests, 48 767 assertions, PASS.
- Unit complète : 1 921 tests, 6 783 assertions, PASS.
- PHPStan 0 erreur, Pint et `git diff --check` PASS.
- PostgreSQL ciblé : 4 tests, 24 assertions, PASS.
- PostgreSQL complet : 600 tests, 2 608 assertions, PASS.
- Sérialisation objet JSON corrigée via `(object) $candidate->payload`.

## [Phase 5.2B — Contracts & Media Attachment Amendment] — 2026-07-28

### Documentation

- Discovery / Blueprint enregistré GO CERTIFIÉ et FERMÉ.
- Contrats fermés Upload, Asset, Processing et Quota.
- Contrats Runtime, HTTP et Event V1 candidats.
- Audit de `AttachReadyMediaAssetV1` et compatibilité F-06.

### Status

- Contracts Foundation GO CERTIFIÉE et FERMÉE.
- `A-5.2B-MEDIA-ATTACHMENT-BOUNDARY-01` GO CERTIFIÉ et FERMÉ.
- Aucune implémentation, migration, interface PHP, route, provider ou test.

## [Phase 5.2B — Media Ingestion Discovery / Blueprint] — 2026-07-28

### Documentation

- Inventaire de la baseline Media et des frontières F-06/F-11/F-15.
- Définition des owners MediaUpload, MediaAsset, MediaProcessing et MediaQuota.
- Stratégies Persistence, Runtime, HTTP, Event et sécurité.
- Matrices d’ownership, de dépendances et registre des risques.
- Identification de `A-5.2B-MEDIA-ATTACHMENT-BOUNDARY-01`, non ouvert et
  bloquant avant implémentation.

### Status

- Discovery / Blueprint représenté pour GO.
- Aucun code, contrat concret, événement, migration, provider, route ou test.
- Phase 5.2A et F-19/F-20 demeurent gelés.

## [Phase 5.2A — GO FINAL Property & Listing Authoring] — 2026-07-27

### Documentation

- Consolidation de tous les jalons Discovery à Public Integration.
- Baseline fonctionnelle et technique définitivement certifiée.
- Matrice finale de compatibilité et baseline qualité.
- Activation des gels exécutoires F-19 et F-20.
- Registres, README, ROADMAP et MASTER BLUEPRINT alignés.

### Status

- Public Authoring Integration GO CERTIFIÉ et FERMÉ.
- Phase 5.2A GO FINAL CERTIFIÉE, FERMÉE et GELÉE.
- F-19 et F-20 CERTIFIÉS, ACTIFS et GELÉS.
- Phase 5.2B — Media Ingestion OUVERTE.
- Aucun changement technique, aucune migration et aucun amendement ouvert.

## [Phase 5.2A — Public Authoring Integration] — 2026-07-27

### Added

- Façade API versionnée `PublicAuthoringJourney`.
- Parcours privé UI `/authoring/workspace`.
- Traduction exclusive vers `PropertyListingAuthoringOperations`.
- Auto-scope IAM, CSRF, idempotence, validation par opération et rate limiting.
- Dépendances frontend verrouillées et build Vite de production.

### Validation

- Unit + Feature + Architecture ciblés : 8 tests, 67 assertions, PASS.
- PostgreSQL 18.x end-to-end : 1 test, 6 assertions, PASS.
- Architecture complète : 626 tests, 48 114 assertions, PASS.
- Suite applicative : 2 816 tests, 56 333 assertions, PASS.
- Build Vite : PASS ; npm audit : 0 vulnérabilité.
- PHPStan : 0 erreur ; Pint et `git diff --check` : PASS.
- Public Authoring Integration GO CERTIFIÉ et FERMÉ.
- Final Certification & Freeze ouvert comme seul jalon autorisé.

## [Phase 5.2A — Authoring Operations Foundation] — 2026-07-27

### Added

- Contrat et orchestrateur `PropertyListingAuthoringOperations`.
- Création complète Listing via `CreateListingDraftV1`.
- Initialisation atomique draft, ownership et portfolio.
- Édition, délégations et soumission contrôlée vers F-01.
- Adaptateur Property placé dans l’enclave de composition inter-module.

### Validation

- Unit + Feature + Architecture ciblés : 8 tests, 49 assertions, PASS.
- PostgreSQL 18.x Operations : 2 tests, 16 assertions, PASS.
- Architecture complète : 622 tests, 47 906 assertions, PASS.
- Suite applicative : 2 808 tests, 56 110 assertions, PASS.
- PHPStan : 0 erreur ; Pint et `git diff --check` : PASS.
- Authoring Operations Foundation GO CERTIFIÉ et FERMÉ.
- Public Authoring Integration ouverte comme seul jalon autorisé.

## [Phase 5.2A — HTTP Foundation] — 2026-07-27

### Added

- Onze endpoints privés Property & Listing Authoring.
- Contrôleur et Runtime HTTP fermés consommant `PropertyListingAuthoringRuntimeV1`.
- Auto-scope IAM, validation stricte et Idempotency-Key obligatoire.
- Autorisation owner/délégation, rate limiting HMAC et réponses `no-store`.
- Tests Feature sécurité, Architecture et PostgreSQL associés.

### Validation

- Feature sécurité + Architecture ciblées : 7 tests, 48 assertions, PASS.
- PostgreSQL 18.x propriétaire : 5 tests, 26 assertions, PASS.
- Architecture complète : 619 tests, 47 752 assertions, PASS.
- Suite applicative : 2 800 tests, 55 945 assertions, PASS.
- PHPStan : 0 erreur ; Pint et `git diff --check` : PASS.
- HTTP Foundation GO CERTIFIÉ et FERMÉ.
- Authoring Operations Foundation ouverte comme seul jalon autorisé.

## [Phase 5.2A — Runtime Foundation] — 2026-07-27

### Added

- Providers Runtime owner-scoped pour PropertyAuthoring et Listing Authoring.
- Composition publique `PropertyListingAuthoringRuntimeV1`.
- Politique Availability déterministe et fail-closed.
- Diagnostics Runtime séparés du catalogue Runtime Health F-14.
- Tests Runtime, Architecture et PostgreSQL associés.

### Validation

- Runtime + Architecture ciblés : 5 tests, 64 assertions, PASS.
- PostgreSQL 18.x propriétaire : 5 tests, 26 assertions, PASS.
- Architecture complète : 616 tests, 47 597 assertions, PASS.
- Suite applicative : 2 793 tests, 55 774 assertions, PASS.
- PHPStan : 0 erreur ; Pint et `git diff --check` : PASS.
- Runtime Foundation GO CERTIFIÉ et FERMÉ.
- HTTP Foundation ouverte comme seul jalon autorisé.

## [Phase 5.2A — Persistence Foundation] — 2026-07-27

### Added

- États, ports, mappers et stores propriétaires pour PropertyAuthoring,
  ListingAuthoringDraft, ListingOwnership et AuthoringPortfolio.
- Migrations additives 056 et 057, séparées par owner.
- Optimistic locking, replay canonique, révisions append-only, délégations et
  checkpoints monotones.
- Tests PostgreSQL de rollback, idempotence et concurrence.

### Validation

- Architecture ciblée : 2 tests, 35 assertions, PASS.
- PostgreSQL 18.x ciblé : 5 tests, 26 assertions, PASS.
- Architecture complète : 613 tests, 47 421 assertions, PASS.
- Suite applicative : 2 788 tests, 55 581 assertions, PASS.
- PHPStan : 0 erreur ; Pint et `git diff --check` : PASS.
- Persistence Foundation GO CERTIFIÉ et FERMÉ.
- Runtime Foundation ouverte comme seul jalon autorisé.

## [Phase 5.2A — Implementation Foundation] — 2026-07-27

### Added

- Frontière Application `CreateListingDraftV1`.
- Command, Result et statuts fermés V1.
- Journal d'intents Listing et migration additive 055.
- Composition transactionnelle PostgreSQL locale avec savepoints.
- Tests contractuels, rollback et concurrence.

### Validation

- Architecture complète : 611 tests, 46 908 assertions, PASS.
- Suite applicative : 2 786 tests, 55 068 assertions, PASS.
- PHPStan : 0 erreur ; Pint et `git diff --check` : PASS.
- PostgreSQL 18.x ciblé : 5 tests, 26 assertions, PASS, résultat terminal
  reproduit deux fois.
- Environnement de test rétabli sous authentification SCRAM.
- Implementation Foundation GO CERTIFIÉ et FERMÉ.

## [A-5.2A-LISTING-CREATION-BOUNDARY-01] — 2026-07-27

### Documentation

- Audit de `CreateDraft`, `ListingRegistry` et de la transaction Listing.
- Contrat public documentaire `CreateListingDraftV1`.
- Résultats fermés et convergence concurrente par intentId/checksum.
- Stratégie de journal d'intents et transaction locale additive.
- Matrice de compatibilité avec F-01, F-02, F-11 et F-14 à F-18.

### Status

- Amendement GO CERTIFIÉ et FERMÉ.
- Implementation Foundation ouverte dans le périmètre autorisé.
- Aucun composant technique ou capacité gelée modifié.

## [Phase 5.2A — Contracts Foundation] — 2026-07-27

### Documentation

- Contrats normatifs des quatre autorités additives.
- Commands, Queries, résultats fermés, invariants, idempotence et concurrence.
- Contrats Event V1, Runtime, HTTP, replay, complétude et handoff.
- Audit de la frontière de création Listing.

### Status

- Contracts Foundation GO CERTIFIÉ et FERMÉ.
- `A-5.2A-LISTING-CREATION-BOUNDARY-01` ouvert pour audit documentaire et
  bloquant avant implémentation.
- Aucun composant technique ou capacité gelée modifié.

## [Phase 5.2A — Property & Listing Authoring Discovery] — 2026-07-27

### Documentation

- Blueprint de la capacité d'authoring Property/Listing.
- Owners, frontières, dépendances et risques.
- Stratégies conceptuelles Persistence, Runtime, Event et HTTP.
- Séparation additive des lifecycles F-01/F-02 et d'IAM F-17/F-18.

### Status

- Discovery / Blueprint GO CERTIFIÉ et FERMÉ.
- Contracts Foundation ouverte.
- Aucun code, contrat, événement, migration, provider, route ou test créé.
- Le jalon Contracts reste fermé jusqu'à la décision d'autorité.

## [Phase 5.1J — GO FINAL Identity & Access Completion] — 2026-07-27

### Status

- 5.1J GO CERTIFIÉE et FERMÉE.
- Phase 5.1 GO FINAL CERTIFIÉE, FERMÉE et GELÉE.
- F-17 Identity & Access Completion et F-18 Migrations IAM 044–054 activés.
- Phase 5.2A Property & Listing Authoring ouverte comme seul jalon autorisé.
- `A-5.1-IAM-ERASURE-01` reste identifié, non ouvert et hors périmètre.

### Validation

- Architecture : 609 tests, 46 639 assertions, PASS.
- Suite applicative : 2 781 tests, 54 791 assertions, PASS.
- PostgreSQL complète : 583 tests, 2 510 assertions, PASS.
- Concurrence Outbox IAM : 20/20 PASS, 100 tests, 640 assertions.
- PHPStan, Pint et `git diff --check` : PASS.

## [A-5.1-IAM-OUTBOX-CONCURRENCY-01] — 2026-07-27

### Fixed

- Convergence simultanée des contraintes uniques `message_id` et `event_id`.
- Préservation de `AlreadyApplied` pour un replay identique.
- Préservation de `DivergentMessage` pour une identité Event réutilisée avec
  un transport différent.

### Validation

- Campagne ciblée : 5 tests, 32 assertions, PASS.
- Répétition concurrente : 20/20 PASS, 100 tests et 640 assertions.
- Amendement GO CERTIFIÉ et FERMÉ ; recertification d'impact de 5.1H satisfaite.
- Réserve concurrente levée ; représentation de 5.1J de nouveau autorisée.

## [Phase 5.1J — Final Certification & Freeze] — 2026-07-27

### Documentation

- Consolidation des GO 5.1A à 5.1I.
- Baseline complète Identity & Access Completion.
- Matrice de compatibilité et spécification de gel.
- Mise à jour des registres de capacités gelées et d'amendements.
- Dossier ayant reçu le GO FINAL Phase 5.1.

### Status

- 5.1I est GO CERTIFIÉE et FERMÉE.
- 5.1J est GO CERTIFIÉE et FERMÉE.
- Phase 5.1 est GO FINAL CERTIFIÉE, FERMÉE et GELÉE.
- `A-5.1-IAM-OUTBOX-CONCURRENCY-01` est GO CERTIFIÉ et FERMÉ.
- Aucun autre amendement bloquant n'est identifié.
- 5.2A est ouverte comme seul jalon autorisé.

## [Phase 5.1I — HTTP Runtime & Security] — 2026-07-27

### Added

- Endpoints IAM propriétaires, validation fermée et auto-scope par session.
- Cookies Secure, HttpOnly, SameSite Strict et réponses non cacheables.
- Anti-énumération login/recovery et rate limiting sans PII.
- Provider HTTP indépendant avec fallback Runtime fail-closed.
- Preuves Feature, sécurité et Architecture.

### Status

- 5.1H est GO CERTIFIÉE et FERMÉE.
- 5.1I est ouverte, implémentée et proposée GO ; 5.1J reste fermée.

## [Phase 5.1H — Outbox Owner & Atomic Event Integration] — 2026-07-27

### Added

- Outbox IAM propriétaire et migration additive 054.
- Writer, reader, claim concurrent, retry, reprise et quarantaine.
- Intégration atomique des opérations 5.1F avec les événements 5.1G.
- Preuves PostgreSQL de non-perte, non-duplication et rollback intégral.

### Status

- 5.1G est GO CERTIFIÉE et FERMÉE.
- 5.1H est ouverte, implémentée et proposée GO ; 5.1I reste fermée.

## [Phase 5.1G — Event Contracts, Transport, Routing & Delivery] — 2026-07-27

### Added

- Six événements IAM V1 minimaux, owners Profile et Closure.
- Transport canonique, checksums et identités déterministes.
- Routing multi-destination et consumers fail-closed.
- Politique replay/retry bornée avec quarantaine des divergences.

### Status

- 5.1F est GO CERTIFIÉE et FERMÉE.
- 5.1G est ouverte, implémentée et proposée GO ; 5.1H reste fermée.

## [Phase 5.1F — Runtime Orchestration / Atomic Operations] — 2026-07-27

### Added

- Orchestrateur IAM déterministe et huit opérations fermées.
- Transaction PostgreSQL multi-owner, savepoints, rollback intégral et journal
  d'idempotence owner-scoped via la migration additive 053.
- Verrouillage concurrent par compte et opération.
- Provider d'orchestration distinct afin de préserver le provider 5.1E gelé.
- Preuves Unit, Feature, Architecture et PostgreSQL, dont une course réelle à
  deux processus.

### Status

- 5.1E est GO CERTIFIÉE et FERMÉE.
- 5.1F est ouverte, implémentée et proposée GO ; 5.1G reste fermée.

## [Phase 5.1E — Runtime Composition & Availability Policy] — 2026-07-27

- 5.1D enregistré GO CERTIFIÉ, FERMÉE.
- Provider propriétaire `IdentityAccessRuntimeServiceProvider`.
- politique Availability V1 composant Account, Status et Closure sans
  persistence propre ;
- lecteur Closure PostgreSQL owner-scoped avec état additif `LegacyOpen` ;
- décisions fail-closed et diagnostics versionnés sans PII ;
- Runtime Health IAM séparé, catalogue historique maintenu à 58 ;
- aucun HTTP, Event, Delivery, Outbox ou orchestrateur métier introduit.
- campagnes complètes : Architecture 599/45 636, applicative 2 753/53 626,
  PostgreSQL 574/2 464, PHPStan/Pint/diff PASS.
- 5.1E a depuis été certifiée GO et fermée ; 5.1F est ouverte.

## [A-5.1-IAM-PERSISTENCE-BOUNDARY-01] — 2026-07-26

- Dépendance `Application → Infrastructure` supprimée.
- `OwnerPersistenceState` devient le type contractuel Application du port.
- Snapshot V1 reste dans son enclave ; un mapper Historical Account produit la
  vue minimale `ProfileClaimsSeedSourceState`.
- Mapper et stores PostgreSQL restent propriétaires des détails Infrastructure.
- Champs, validations, résultats fermés, SQL, migrations et comportements
  certifiés préservés.
- Amendement GO CERTIFIÉ et FERMÉ.
- 5.1D représentée sans changement métier pour certification définitive.
- 5.1E reste fermée jusqu'au prononcé d'autorité sur 5.1D.

## [Phase 5.1D — Profile Claims Seed & Authority Cutover] — 2026-07-26

### Construction

- Seed exclusivement alimenté par `HistoricalAccountPersistenceSnapshotV1`.
- Normalisation `iam-profile-v1`, protection injectée, fingerprints HMAC et
  IDs déterministes.
- Migration additive 052 : runs, manifest, quarantaine et registre d'autorité,
  sans FK cross-domain ni cascade.
- Cutover transactionnel `Historical → Profile`, rapport sans PII, rejeu
  idempotent et rollback manifesté.
- Politique fermée de divergences et quarantaine sans Profile/Claim partiel.

### Gouvernance

- 5.1C enregistré GO CERTIFIÉ, FERMÉE.
- 5.1D est le seul jalon ouvert et représenté à l'autorité.
- `A-5.1-IAM-PERSISTENCE-BOUNDARY-01` est GO CERTIFIÉ, FERMÉ.
- 5.1E reste fermée.
- Aucun composant historique, Runtime, Provider, HTTP, Event, Delivery ou
  Outbox modifié.

## [Phase 5.1C — Persistence Foundation] — 2026-07-26

### Conception

- Huit persistences propriétaires attribuées : Attempts, Sessions, Recovery,
  Profile, Claims, Contact Changes, Revisions et Closure.
- Migrations additives 044–051 réservées dans un schéma
  `identity_access_completion`, sans FK vers Historical Account.
- Snapshots, mappers, stores/repositories, résultats persistence, rétention et
  contraintes définis par owner.
- Optimistic locking, idempotence, rollback, unique claims et session
  checkpoints spécifiés.
- Campagne PostgreSQL 18.x et scénarios concurrents définis.

### Implémentation

- Migrations et rollbacks owner-scoped 044–051 matérialisés dans
  `identity_access_completion`, sans FK cross-domain ni cascade.
- Snapshot persistence, mapper déterministe à allow-list et huit stores
  PostgreSQL ajoutés.
- Optimistic locking, idempotence `intent_id + checksum`, rollback externe,
  révisions append-only et checkpoint session monotone implémentés.
- Tests ciblés : Unit/Architecture 4 tests et 103 assertions ; PostgreSQL 18.x
  6 tests et 25 assertions ; PHPStan 0 erreur.

### Statut

- 5.1B enregistré GO CERTIFIÉ, FERMÉE.
- 5.1C est GO CERTIFIÉE et FERMÉE par prononcé d'autorité.
- 5.1D reste fermée.
- Aucun Runtime, Provider, HTTP, Delivery, Outbox, Seed, Cutover ou Erasure
  introduit.

## [Phase 5.1B — Contracts Foundation] — 2026-07-26

### Contrats documentaires

- Authentication et Attempts/Lockout : résultats internes/publics séparés,
  credential boundary, enumeration resistance et idempotence.
- Sessions : rotation, expiration, invalidation checkpoint et concurrence ;
  remember-me exclu de V1.
- Recovery : challenge dédié, usage unique, changement via use case historique
  et invalidation des sessions.
- Profile, Claims, Contact Changes et Revisions : autorité, unicité,
  revérification, anti-takeover, cutover et historique.
- Closure et Availability : Closed orthogonal à Suspended, composition
  fail-closed et dépendances non circulaires.
- Event Catalog limité à trois Profile events et trois Closure events sans
  PII ni secret.

### Gouvernance

- 5.1A enregistré GO CERTIFIÉ, FERMÉE.
- 5.1B proposé GO.
- 5.1C reste fermé jusqu'au prononcé.
- Aucun code, interface PHP, migration, Event class, Runtime, Outbox, HTTP,
  Provider, test ou composant Laravel créé ou modifié.

## [Phase 5.1A — Identity & Access Completion Discovery] — 2026-07-26

### Gouvernance

- Profile et Closure enregistrés GO CERTIFIÉS, FERMÉS.
- Phase 5.1 officiellement OUVERTE.
- `A-5.1-IAM-ERASURE-01` maintenu identifié, non ouvert et hors 5.1.

### Blueprint

- Authentication, attempts/lockout, sessions, recovery, User Profile,
  Identity Claims, Contact Changes, Profile Revisions et Account Closure
  attribués à des autorités additives IdentityAccess.
- Frontières Account/Registry/Snapshot/041–043/Status V1/Runtime 58/HTTP/Outbox
  protégées.
- Roadmap 5.1A–5.1J, matrices de dépendances/risques et neuf gates préventifs
  établis.
- 5.1A proposé GO ; 5.1B Contracts reste fermé jusqu'au prononcé.
- Aucune implémentation, migration, Event, contrat, Runtime, Outbox, HTTP,
  Provider ou test introduit.

## [A-5.1-IAM-PROFILE-01 & CLOSURE-01 — Boundary Audits] — 2026-07-26

### Profile

- Modèle User Profile additif proposé, identifié par `AccountId`.
- Identity Claim Registry proposée pour préserver l'unicité globale des emails
  et téléphones sans modifier Snapshot V1 ou migration 042.
- Revérification, anti-takeover, historique et consumers cadrés.
- Verdict : GO proposé.

### Closure

- `Closed`, `Deleted`, `Anonymized` et `Suspended` distingués.
- Account Closure additif proposé avec priorité d'accès, rétention, session
  invalidation et références cross-domain conservées.
- `A-5.1-IAM-ERASURE-01` identifié avant toute anonymisation ou destruction de
  Historical Account.
- Verdict : GO proposé.

### Gouvernance

- `A-5.1-IAM-01` enregistré NO GO CERTIFIÉ, FERMÉ.
- Profile et Closure enregistrés OUVERTS.
- Phase 5.1 reste fermée.
- Aucun code, Event, contrat, migration, Runtime, Outbox, HTTP, Provider, test
  ou composant Laravel modifié.

## [A-5.1-IAM-01 — Account Frozen Boundary Amendment] — 2026-07-26

### Audité

- Audit exhaustif de `Account`, `AccountRegistry`, Historical Account,
  Account Status, Event V1, migrations 041–043, Runtime, Outbox et HTTP.
- Authentification, connexion, récupération, changement de mot de passe,
  rôles, consentements, vérifications email/téléphone et sessions classés
  compatibles avec une tranche additive.
- Profil utilisateur modifiable et fermeture de compte classés incompatibles
  sans amendements complémentaires.

### Décision certifiée

- `A-5.1-IAM-01` : NO GO CERTIFIÉ, FERMÉ.
- Amendements proposés : `A-5.1-IAM-PROFILE-01` et
  `A-5.1-IAM-CLOSURE-01`.
- Phase 5.1 reste fermée.
- Aucun code, contrat, migration, Runtime, Outbox, HTTP, Provider ou test
  modifié.

## [Phase 5.0B — Baseline Alignment & Governance] — 2026-07-26

### Aligné

- Réalignement de `README.md`, `ROADMAP.md`, `CHANGELOG.md` et
  `docs/MASTER-BLUEPRINT.md` sur l'état réel certifié après 5.0A.
- Création de la baseline documentaire unique, du registre des capacités
  gelées, du registre des amendements et des règles de certification 5.x.
- Formalisation des règles d'ouverture, d'impact, de recertification et de
  clôture d'un amendement versionné.

### Baseline qualité

- Architecture : 592 tests et 44 523 assertions, PASS.
- Unit : 1 891 tests et 6 609 assertions, PASS.
- Feature : 249 tests et 1 339 assertions, PASS.
- Foundation : 1 test et 4 assertions, PASS.
- Total applicatif : 2 733 tests et 52 475 assertions, PASS.
- Pint : PASS ; PHPStan/Larastan : 0 erreur ; `git diff --check` : PASS.
- Runtime Health : baseline certifiée `Healthy`, 58 capacités.
- PostgreSQL : gate distincte conservée ; la campagne complète nécessite
  PostgreSQL 18.x et ne peut être remplacée par SQLite ou par un timeout.

### Gouvernance

- Phase 5.0A enregistrée `GO CERTIFIÉ`, `FERMÉE`.
- Phase 5.0B enregistrée `GO CERTIFIÉ`, `FERMÉE`.
- Aucune fonctionnalité métier, migration, contrat, événement, modification
  Runtime ou Outbox introduite.
- Phase 5.1 reste interdite avant certification 5.0B et amendement versionné
  préalable pour toute évolution de la capacité Account gelée.

## [Phase 5.0A — Global Domain Audit] — 2026-07-26

### Certifié

- Audit global, Domain Map, Dependency Matrix, Ownership Matrix, Event Catalog
  et roadmap Phase 5 vers Production acceptés comme plan directeur officiel.
- Statut : `GO CERTIFIÉ`, `FERMÉE`.
- Inventaire consolidé de 16 capacités restantes et de neuf chaînes lifecycle
  gelées.

## [Phase 2 — Sprint 2.8 : MediaCollection PostgreSQL Vertical Slice] — 2026-07-18

### Ajouté

- Snapshots MediaCollection/MediaItem, mapper explicite et intégrité persistante.
- Migration `004_media.sql`, schéma `media`, réservation durable de `MediaId` et contraintes locales.
- Transaction locale, quatrième Repository PostgreSQL, harness partagé, intégration et trois concurrences réelles.

### Garanti

- Réutilisation sans duplication des 14 contrats Fake/PostgreSQL, rollback total et optimistic locking.
- Mapping exact des items et des dates avec microsecondes/offset, reconstruction sans événements.
- Aucun cinquième Repository, composant générique, Outbox, Dispatcher ou artefact web.
- Certification PostgreSQL réelle : 91 tests, 415 assertions, zéro erreur et zéro échec en 17,275 secondes.
- Correction minimale du bind `is_primary` en `1` ou `0`; cause technique chaînée sans fuite SQL dans le message public.
- Trois concurrences Media couvertes, aucun secret exposé et aucune règle métier ou contrainte affaiblie.

## [Phase 2 — Sprint 2.7 : MediaCollection Registry Contract Foundation] — 2026-07-18

### Ajouté

- Accesseur Domain `lastChangedAt()` en lecture seule et preuves temporelles ciblées.
- Contrat partagé `MediaCollectionRegistryContract`, harness backend-agnostique et entrée Fake minimale.
- Profil documenté des réservations MediaCollectionId/MediaId, états enfants, rollback, version et événements.
- Garde-fou interdisant toute Infrastructure Media avant GO explicite.

### Confirmé

- MediaId est réservé globalement et définitivement par `saveWithMediaReservation`.
- Checksum et ordre restent des invariants locaux; Removed et Archived sont terminaux.
- Aucun Repository, mapper, snapshot, transaction, SQL ou migration Media n’est créé.

## [Phase 2 — Sprint 2.6 : PropertyRegistry PostgreSQL Vertical Slice] — 2026-07-18

### Ajouté

- Accesseur Domain `lastChangedAt()` strictement en lecture seule et ses preuves ciblées.
- Snapshots Property/Address, mapper explicite et intégrité persistante.
- Migration `003_property.sql`, schéma propriétaire, réservation durable de référence et Address.
- Transaction locale, troisième Repository PostgreSQL, harness partagé, intégrations et trois concurrences réelles.

### Garanti

- PropertyId et PropertyReference permanents, rollback total et optimistic locking.
- Mapping exact de Address et de la date avec microsecondes/offset.
- Aucun quatrième Repository, composant générique, Outbox, Dispatcher ou artefact web.

## [Phase 2 — Sprint 2.5 : Property Registry Contract Foundation] — 2026-07-17

### Ajouté

- Suite abstraite backend-agnostique `PropertyRegistryContract` et harness dédié.
- Entrée Fake minimale réutilisant tous les scénarios sans duplication.
- Profil contractuel documenté : identité, business key, version, rollback, événements, Address et terminalité.
- Garde-fou interdisant toute Infrastructure Property avant GO explicite.

### Confirmé

- `PropertyId` et `PropertyReference` sont des réservations permanentes et atomiques à l’ajout.
- `AddressId` reste local au Root; Archived est terminal.
- Aucun Repository, mapper, snapshot, transaction, SQL ou migration Property n’est créé.

## [Phase 2 — Sprint 2.4 : ListingRegistry PostgreSQL Vertical Slice] — 2026-07-17

### Ajouté

- Snapshots et mapper explicites Listing, intégrité persistante et tests unitaires.
- Schéma propriétaire `listing_lifecycle`, migration `002_listing.sql`, tables Root et révisions append-only.
- Transaction locale et deuxième Repository PostgreSQL concret.
- Harness réutilisant les 14 contrats partagés, intégrations/rollback et deux concurrences réelles.

### Garanti

- Réservation permanente de `ListingId`, unicité locale de `ListingRevisionId`, optimistic locking et rollback total.
- Préservation des événements appelants, reconstruction sans événement et offsets temporels exacts.
- Aucun troisième Repository, composant générique, Outbox, Dispatcher, Unit of Work globale ou artefact web.

## [Phase 2 — Sprint 2.3D : PostgreSQL Repository Foundation Standardization] — 2026-07-17

### Ajouté

- Guide officiel dérivé de la tranche AdministrativeAction certifiée, checklist de validation et conventions de nommage PostgreSQL.
- Garde-fous déterministes interdisant Repository/Mapper/Snapshot génériques ou partagés, types de persistance hors Infrastructure et SQL/PDO hors Infrastructure de module.

### Décidé

- AdministrativeAction devient le modèle officiel des futurs Repositories PostgreSQL, sans généralisation de son modèle métier.
- Chaque tranche reste locale à un Aggregate, explicitement autorisée et validée contre le même contrat partagé Fake/PostgreSQL.
- Aucun Repository, mapper, snapshot, SQL ou migration Listing n’est créé pendant ce sprint.

## [Phase 2 — Sprint 2.3C : certification AdministrativeAction et contrats Listing] — 2026-07-17

### Certifié

- Sprint 2.3B clos sur PostgreSQL 18.4 réel : 27/27 tests, 94 assertions, 19 contrats partagés, 6 intégrations/contraintes/rollback et 2 concurrences réelles.
- Un seul gagnant est confirmé pour les courses `add` et `save(expectedVersion)`, sans mutation partielle.
- PHP 8.5.8, `pdo_pgsql` et `pgsql` actifs ; aucun fallback SQLite.
- Suite complète 606/606 (12 973 assertions), Architecture 23/23 (11 439 assertions), Pint, Larastan et `composer quality` verts lors de la certification.

### Ajouté

- Fondation contractuelle backend-agnostique de `ListingRegistry`, harness Fake minimal et garde-fous interdisant une tranche Listing prématurée.
- Exemple PostgreSQL sans secret ; la chaîne officielle reste `composer test:postgresql` → `phpunit.postgresql.xml` → `APPART_TEST_PG_*` → `PostgreSqlTestEnvironment` → migration `001_administrative_action.sql` → contrats/intégration/concurrence.

Toutes les évolutions documentaires du projet APPART.SN REBUILD sont consignées dans ce fichier.

## [Phase 2 — Sprint 2.3B : AdministrativeAction PostgreSQL Vertical Slice] — 2026-07-17

### Ajouté

- Snapshot et mapper explicites AdministrativeAction.
- Migration PostgreSQL propriétaire avec Root, Approval, Decision et historique append-only.
- Premier et unique Repository PostgreSQL, optimistic locking et transaction locale.
- Harness contractuel PostgreSQL sans duplication des 19 scénarios.
- Tests de mapping, rollback, contraintes et concurrence à deux connexions/processus.
- Configuration `composer test:postgresql` sans fallback.

### Limite de validation

- PostgreSQL 18.x n’est pas installé dans l’environnement courant ; le téléchargement officiel a échoué avec HTTP 403. La tranche reste NO GO jusqu’à exécution réelle de la suite PostgreSQL.

## [Phase 2 — Sprint 2.3A : Shared Registry Contract Foundation] — 2026-07-17

### Ajouté

- Suite abstraite réutilisable `AdministrativeActionRegistryContract` et harness indépendant du backend.
- Harness Fake et classe d’entrée PHPUnit sans duplication des scénarios.
- Dix-neuf scénarios contractuels couvrant lecture, détachement, événements, version, historique, états terminaux, conflits et rollback.
- Garde-fous dédiés aux contrats partagés et à la décision Product.

### Corrigé

- `FakeAdministrativeActionRegistry` stocke désormais un snapshot sans événements résiduels tout en préservant les événements de l’instance appelante.

### Gouvernance

- ADR-1006 accepté : Product reste un catalogue administré immuable.
- Reservation Strategy et Contract Test Blueprint normatifs.
- GO limité aux tests contractuels Fake ; aucun GO Repository PostgreSQL avant le bilan qualité complet du Sprint 2.3A.

## [Phase 2 — Sprint 2.2 : Infrastructure Baseline Consolidation] — 2026-07-17

### Ajouté

- Inventaire exhaustif des modules, Aggregates, objets DDD, ports, cas d’usage et fakes.
- Matrice des 14 Aggregate Roots et préparation de leur future persistance.
- Audit documenté des frontières et baseline de couverture.
- Tests d’architecture déterministes interdisant Laravel dans Domain, Eloquent, SQL/accès base, Repository concret, dépendances inter-modules et dépendances inverses.
- Roadmap et étapes d’entrée du Sprint 2.3.

### Décision

- NO GO conditionnel pour l’implémentation PostgreSQL tant que le statut de `Product` et les réservations uniques ne sont pas validés.
- Aucun des huit blueprints Infrastructure normatifs n’a été modifié.

## [Sprint 6 — Domain 06 : Media Foundation] — 2026-07-16

### Ajouté

- Implémentation du domaine pur `Media` autour de l’Aggregate Root `MediaCollection`.
- Ajout de l’entité historisée `MediaItem` et des Value Objects d’identité, type, statut, checksum, ordre, légende et source.
- Ajout des événements d’ajout, retrait, réordonnancement, changement de légende, sélection principale et archivage.
- Ajout des sept cas d’usage, d’un `MediaCollectionRegistry` détaché et versionné et d’un contrat abstrait `PropertyCatalog`.
- Garantie atomique de l’appartenance globale d’un `MediaId` à une seule collection.
- Ajout des tests couvrant principal unique, remplacement explicite, historique, doublons, concurrence, rollback et absence de rejeu.

### Contraintes respectées

- Aucun autre domaine modifié ou directement importé par Media.
- Aucune annonce, cycle de vie, SEO, prix, paiement, API, persistance ou dépendance Laravel introduite.

## [Sprint 5.1 — Real Estate Catalog Hardening] — 2026-07-16

### Corrigé

- Ajout d’une politique métier déterministe `PropertyTypePolicy` gouvernant les sept types de biens.
- Ajout d’un `BusinessYear` explicite et refus de toute année de construction future.
- Formalisation des règles propres aux terrains, logements, bureaux, commerces et biens génériques.
- Correction de l’égalité physique des adresses indépendamment de `AddressId`.
- Remplacement du booléen géographique par des états explicites : utilisable, inexistant, désactivé, fusionné ou non adressable.
- Extension des tests aux politiques par type, au temps métier, aux adresses physiques identiques et aux états géographiques.

### Contraintes respectées

- Seul RealEstateCatalog, ses tests et ce journal ont été renforcés.
- Aucun autre domaine, framework, adapter, stockage, API ou composant Laravel introduit.

## [Sprint 5 — Domain 05 : Real Estate Catalog Foundation] — 2026-07-16

### Ajouté

- Implémentation du domaine pur `RealEstateCatalog` autour de l’Aggregate Root `Property`.
- Ajout de l’entité `Address` et des Value Objects décrivant identité, référence, type, surface, pièces, année et localisation.
- Ajout des événements d’enregistrement, mise à jour, archivage, changement d’adresse et changement de surface.
- Ajout des quatre cas d’usage et d’un `PropertyRegistry` atomique, détaché et versionné.
- Ajout d’un contrat géographique abstrait garantissant qu’une adresse utilise un lieu disponible sans dépendance directe à Geography.
- Ajout des tests couvrant doublons, invariants, archivage terminal, concurrence, rollback et absence de rejeu d’événements.

### Contraintes respectées

- Aucun autre domaine modifié ou directement référencé par RealEstateCatalog.
- Aucune annonce, cycle de vie, média, SEO, prix, paiement, API, persistance ou dépendance Laravel introduite.

## [Sprint 4.1 — Professionals Hardening] — 2026-07-16

### Corrigé

- Garantie globale et atomique d’un propriétaire unique pour chaque `EstablishmentId`, y compris après retrait historique.
- Distinction explicite des conflits de `ProfessionalId`, `RegistrationNumber`, `EstablishmentId` et de version concurrente.
- Ajout d’une sauvegarde conditionnelle combinant atomiquement la réservation d’établissement et la mutation de l’Aggregate.
- Formalisation de la politique des événements : production sur l’instance détachée, libération unique et snapshots enregistrés sans événements transitoires.
- Renforcement du rollback afin qu’un échec ne rende visible ni mutation ni réservation.
- Extension des tests Professionals aux scénarios multi-professionnels, propriété globale, rechargement et absence de rejeu.

### Contraintes respectées

- Seul Professionals, ses tests et ce journal ont été renforcés.
- Aucun autre domaine, framework, adapter, stockage, API ou composant Laravel introduit.

## [Sprint 4 — Domain 04 : Professionals Foundation] — 2026-07-16

### Ajouté

- Implémentation du domaine pur `Professionals` autour de l’Aggregate Root `Professional`.
- Ajout des entités historisées `Establishment` et `RepresentativeMandate`.
- Ajout des Value Objects d’identité, d’immatriculation, de nom, de représentant et de rôle de mandat.
- Ajout des sept événements métier couvrant l’enregistrement, les établissements, les mandats, la suspension et la réactivation.
- Ajout des sept cas d’usage et d’un contrat `ProfessionalRegistry` atomique, détaché et versionné.
- Ajout des tests couvrant les invariants, doublons, conflits concurrents, rollback et événements.

### Contraintes respectées

- Aucun autre domaine modifié ou référencé par Professionals.
- Aucune annonce, compte, permission, dépendance Laravel, persistance, API, migration ou adapter introduit.

## [Sprint 3 — Domain 03 : Administration Audit Foundation] — 2026-07-16

### Ajouté

- Implémentation du domaine pur `AdministrationAudit` autour de l’Aggregate Root `AdministrativeAction`.
- Ajout des entités `Approval`, `Decision` et `AuditEntry`, avec conservation de la preuve chronologique au sein de l’Aggregate.
- Ajout des Value Objects d’identité administrative, d’acteur, de cible, de type d’action, de motif, d’approbation et de décision.
- Ajout des événements `AdministrativeActionRecorded`, `AdministrativeActionApproved`, `AdministrativeActionRejected`, `AuditEntryRecorded` et `FourEyesSatisfied`.
- Ajout des cas d’usage de création, ajout du motif, enregistrement, approbation et rejet via un contrat abstrait de registre versionné.
- Ajout de 25 tests AdministrationAudit couvrant le cycle de décision, la règle des quatre yeux, les preuves, événements, invariants et erreurs métier.

### Contraintes respectées

- Seul AdministrationAudit et ses tests ont été développés pour ce sprint.
- Le domaine enregistre et orchestre les décisions administratives sans exécuter les effets appartenant aux autres domaines.
- Aucune dépendance vers Geography, IdentityAccess, Laravel, une persistance, un adapter, une API ou PostgreSQL n’a été introduite.

## [Sprint 2.1 — Identity Access Hardening] — 2026-07-16

### Corrigé

- Encapsulation complète des hashes et tokens, avec sérialisation interdite et représentation de diagnostic expurgée.
- Validation structurelle générique des hashes encodés sans dépendance à un algorithme.
- Liaison des tokens à leur canal et ajout du remplacement invalidant immédiatement le token précédent.
- Suppression du temps implicite dans les opérations métier et contrôle de cohérence chronologique.
- Ajout d’une version d’Aggregate et d’une sauvegarde conditionnée par la version attendue.
- Détachement profond des Aggregates dans le registre de test pour empêcher toute mutation visible avant sauvegarde réussie.
- Adoption explicite de l’option B : la suspension révoque tous les rôles actifs et la réactivation ne les restaure pas.
- Extension à 43 tests IdentityAccess, incluant secrets, renouvellement, mauvais canal, token consommé, rollback et concurrence.

### Contraintes respectées

- Seul IdentityAccess et ses tests ont été renforcés.
- Aucun autre domaine, framework, adapter, stockage, API ou composant Laravel introduit.

## [Sprint 2 — Domain 02 : Identity Access Foundation] — 2026-07-16

### Ajouté

- Implémentation du domaine pur `IdentityAccess` autour de l’Aggregate Root `Account`.
- Ajout des credentials, vérifications email et téléphone, consentements et attributions de rôles historisées.
- Ajout des Value Objects d’identité, de coordonnées, de credential, de vérification et de rôle.
- Ajout des huit événements métier demandés, complétés par les faits de consentement accordé et retiré, et des cas d’usage associés.
- Ajout d’un contrat `AccountRegistry` dont l’ajout atomique garantit conceptuellement l’unicité de l’identifiant, de l’email et du téléphone.
- Ajout de 33 tests IdentityAccess couvrant le cycle du compte, les vérifications, rôles, consentements, invariants et erreurs.

### Contraintes respectées

- Aucun autre domaine modifié ou référencé par IdentityAccess.
- Aucune dépendance Laravel, persistance, session, route, middleware, API, modèle Eloquent ou adapter créé.

## [Sprint 1.1 — Geography Hardening] — 2026-07-16

### Corrigé

- Résolution du parent réel depuis le registre avant toute création de lieu.
- Validation du type réel, de l’état actif, de l’absence de fusion et du pays du parent.
- Détection des auto-rattachements et des cycles hiérarchiques indirects.
- Remplacement des vérifications préalables d’unicité par une opération conceptuellement atomique `add` dans `PlaceRegistry`.
- Enrichissement de `PlaceCreated` avec le code, le pays, le parent et les coordonnées officiels.
- Extension à 56 tests Geography couvrant hiérarchie réelle, cycles, fusion, événements, frontières et unicités.

### Contraintes respectées

- Seul le domaine Geography et ses tests ont été renforcés.
- Aucun autre domaine, adapter, accès PostgreSQL, composant Laravel ou API modifié ou créé.

## [Sprint 1 — Domain 01 : Geography Foundation] — 2026-07-16

### Ajouté

- Implémentation du domaine pur et autonome `Geography` autour de l’Aggregate Root `Place`.
- Ajout des Value Objects `PlaceId`, `PlaceName`, `PlaceCode`, `PlaceType`, `Coordinates` et `CountryCode`.
- Ajout des concepts internes `AdministrativeDivision` et `GeographicAlias`.
- Ajout des événements `PlaceCreated`, `PlaceRenamed`, `PlaceMerged`, `PlaceDisabled` et `PlaceEnabled`.
- Ajout des cas d’usage de création, renommage, fusion, désactivation et activation via un contrat abstrait de registre.
- Ajout de 37 tests Geography couvrant les parcours, invariants, identifiants et erreurs métier.

### Contraintes respectées

- Aucun autre domaine modifié ou référencé.
- Aucune dépendance Laravel introduite dans le domaine.
- Aucune route, vue, API, migration, persistance, modèle Eloquent, adapter ou service Laravel créé.

## [Sprint J0 — Foundation v1.0] — 2026-07-16

### Ajouté

- Création du projet sous PHP 8.5.x et Laravel 13.x, avec PostgreSQL 18.x comme plateforme de persistance cible.
- Création de la structure du monolithe modulaire et des treize enveloppes de domaines vides.
- Séparation physique du domaine (`src/`) et de la périphérie Laravel (`app/`).
- Configuration du socle qualité avec Pint, Larastan, PHPUnit, audit des dépendances et tests d’architecture.
- Ajout de protections vérifiant les treize modules, la structure validée et l’indépendance du domaine vis-à-vis de Laravel.
- Configuration locale minimale sans création de base, de schéma ou de migration.

### Contraintes respectées

- Aucune fonctionnalité métier développée.
- Aucun Aggregate, modèle métier, contrôleur métier ou API métier créé.
- Aucune règle de publication, SEO, paiement, authentification ou migration Legacy implémentée.
- Aucun secret réel ajouté au dépôt.

## [Sprint 7 — Document 5 : ADR-1005 Repository Governance] — 2026-07-16

### Ajouté

- Création de `docs/implementation/ADR-1005-REPOSITORY-GOVERNANCE.md`.
- Adoption d’une branche `main` protégée et de branches de travail courtes, sans branche `develop` permanente.
- Définition des conventions de commit et des Pull Requests obligatoires.
- Classification des changements selon quatre niveaux de risque et formalisation des revues associées.
- Définition des Quality Gates universels, métier, architecture, données et sécurité.
- Formalisation de la CI conceptuelle, des protections de branches et de la fusion par squash.
- Définition des règles de retour, urgence, Definition of Ready et Definition of Done.
- Renforcement de la politique de dette technique et de gouvernance des dépendances.
- Documentation des mesures, critères d’acceptation, questions ouvertes et références aux ADR validés.

### Contraintes respectées

- Livrable exclusivement documentaire.
- Aucun code ni projet Laravel créé.
- Aucune configuration GitHub ou protection de branche réelle créée.
- Aucun pipeline ou configuration CI créé.
- Aucun document normatif existant modifié.
- Seuls le nouvel ADR et le présent changelog ont été modifiés.

## [Sprint 7 — Document 4 : ADR-1004 Authentication and Secrets] — 2026-07-16

### Ajouté

- Création de `docs/implementation/ADR-1004-AUTHENTICATION-AND-SECRETS.md`.
- Définition du modèle d’identité séparant Compte, Professionnel, Mandat, comptes internes et Système.
- Décision d’une authentification web par session et de niveaux d’assurance proportionnés aux acteurs.
- Formalisation des politiques de mots de passe, MFA, résistance au phishing, sessions et step-up.
- Définition de l’autorisation contextuelle par action, ressource, ownership, état et quatre yeux.
- Définition de la séparation des environnements et d’une capacité dédiée de gestion des secrets.
- Formalisation des rotations, récupérations d’accès, comptes techniques, identités applicatives et accès d’urgence.
- Documentation de la journalisation, réponse aux incidents, risques, critères d’acceptation et questions ouvertes.
- Traçabilité vers ADR-1000, ADR-1001, ADR-1002 et ADR-1003.

### Contraintes respectées

- Livrable exclusivement conceptuel et documentaire.
- Aucun code ni projet Laravel créé.
- Aucune commande Composer lancée.
- Aucun package, fichier `.env`, configuration ou secret créé.
- Aucun document normatif existant modifié.
- Seuls le nouvel ADR et le présent changelog ont été modifiés.

## [Sprint 7 — Document 3 : ADR-1003 Physical Module Structure] — 2026-07-16

### Ajouté

- Création de `docs/implementation/ADR-1003-PHYSICAL-MODULE-STRUCTURE.md`.
- Séparation officielle du cœur indépendant sous `src` et de la périphérie Laravel sous `app`.
- Positionnement des treize domaines sous `src/Modules` avec enveloppes J0 visibles et vides.
- Définition des positions futures des interfaces, adaptateurs, projections, tests, documentation et ADR.
- Isolement des outils temporaires sous `tools/temporary` et de Migration Legacy dans des zones supprimables.
- Formalisation des exclusions du socle partagé, dépendances physiques et règles de visibilité.
- Définition des politiques futures d’espaces de noms, nommage et évolution structurelle.
- Documentation des critères d’acceptation et questions ouvertes avant J0.

### Contraintes respectées

- Livrable exclusivement conceptuel et documentaire.
- Aucun projet Laravel ni dossier applicatif créé.
- Aucune commande Composer lancée.
- Aucun code, fichier de configuration, migration ou package créé.
- Aucun espace de noms définitif imposé.
- Aucun document normatif existant modifié.
- Seuls le nouvel ADR et le présent changelog ont été modifiés.

## [Sprint 7 — Document 2 : ADR-1002 Initial Persistence Platform] — 2026-07-16

### Ajouté

- Création de `docs/implementation/ADR-1002-INITIAL-PERSISTENCE-PLATFORM.md`.
- Sélection officielle de PostgreSQL 18.x comme plateforme relationnelle principale.
- Comparaison argumentée de PostgreSQL, MySQL et MariaDB sur intégrité, concurrence, transactions, contraintes, indexation, JSON, recherche et exploitation.
- Définition des politiques de transaction, intégrité, contraintes, concurrence, sauvegarde et restauration.
- Formalisation des règles d’évolution, données historiques, Migration Legacy, performance et sécurité.
- Reconnaissance de MySQL 8.4 LTS comme alternative de repli conditionnelle.
- Documentation des conséquences, risques, critères d’acceptation et questions ouvertes.
- Traçabilité explicite vers ADR-1000 et ADR-1001.

### Contraintes respectées

- Livrable exclusivement conceptuel et documentaire.
- Aucun code ni projet Laravel créé.
- Aucune commande Composer lancée.
- Aucune base de données, table, structure SQL, migration Laravel, modèle Eloquent ou fichier de configuration créé.
- Aucun document normatif existant modifié.
- Seuls le nouvel ADR et le présent changelog ont été modifiés.

## [Sprint 7 — Document 1 : ADR-1001 Runtime and Framework Selection] — 2026-07-16

### Ajouté

- Création de `docs/implementation/ADR-1001-RUNTIME-AND-FRAMEWORK-SELECTION.md`.
- Sélection officielle de PHP 8.5.x et Laravel 13.x pour le futur projet.
- Comparaison des branches PHP 8.2 à 8.5 et Laravel 11 à 13 selon les calendriers officiels.
- Vérification de la compatibilité PHP/Laravel et définition d’un repli PHP 8.4 strictement conditionnel.
- Formalisation des politiques de support, mise à niveau, rétrocompatibilité, LTS, fin de support et corrections de sécurité.
- Définition des politiques relatives aux dépendances PHP, extensions et packages Laravel.
- Documentation des risques, alternatives, conséquences, critères d’acceptation et questions ouvertes.
- Traçabilité explicite vers ADR-1000 et les décisions techniques restant ouvertes.

### Contraintes respectées

- Livrable exclusivement documentaire.
- Aucun code ni projet Laravel créé.
- Aucune commande Composer lancée.
- Aucune migration, API, structure physique ou fichier de configuration créé.
- Aucun document normatif existant modifié.
- Seuls le nouvel ADR et le présent changelog ont été modifiés.

## [Sprint 6 — Laravel Project Plan] — 2026-07-16

### Ajouté

- Création de `docs/implementation/LARAVEL-PROJECT-PLAN.md`.
- Décision du nom officiel, de l’identifiant technique et de l’arborescence générale future.
- Définition des treize enveloppes de domaines créées vides à J0.
- Formalisation de l’ordre exact de création du futur projet et des portes de blocage.
- Distinction entre composants Laravel immédiats et capacités volontairement différées.
- Décision de l’ordre initial : Géographie, Identité et accès, puis Administration et audit.
- Définition de la séquence complète des treize domaines et de leurs prérequis.
- Formalisation des critères autorisant la première ligne de code et des contrôles précédant le premier commit.
- Documentation des risques J0, critères d’acceptation et questions ouvertes.

### Contraintes respectées

- Livrable exclusivement préparatoire et documentaire.
- Aucun code ni projet Laravel créé.
- Aucune commande Composer lancée.
- Aucun contrôleur, modèle, migration, API, package ou fichier de configuration créé.
- Aucun document normatif existant modifié.
- Seuls le nouveau Laravel Project Plan et le présent changelog ont été modifiés.

## [Sprint 5 — Document 1 : ADR-1000 Technical Foundation] — 2026-07-16

### Ajouté

- Création de `docs/implementation/ADR-1000-TECHNICAL-FOUNDATION.md`.
- Confirmation du monolithe modulaire, de l’indépendance du Domaine et des frontières validées.
- Définition des critères de sélection et de version pour PHP, Laravel, base de données, cache, Recherche, Queue, médias, tests et observabilité.
- Formalisation des principes futurs de modularité, organisation, nommage, dépendances et configuration.
- Définition des politiques de secrets, journalisation, erreurs et évolutions applicatives.
- Définition des exigences de qualité, revue, tests, performance, sécurité, packages tiers et dette technique.
- Création des critères imposant de futurs ADR et d’une roadmap technique progressive.
- Documentation des décisions ouvertes, risques majeurs, critères d’acceptation et questions ouvertes.

### Contraintes respectées

- Livrable exclusivement documentaire, sans description de réalisation.
- Aucun code ni projet Laravel créé.
- Aucune commande Composer lancée.
- Aucun fichier de configuration, migration exécutable ou API créé.
- Aucun document normatif existant modifié.
- Seuls le nouvel ADR et le présent changelog ont été modifiés.

## [Sprint 4 — Document 3 : Aggregate Boundaries] — 2026-07-16

### Ajouté

- Création de `docs/architecture/AGGREGATE-BOUNDARIES.md`.
- Classement des candidats du Domain Mapping entre Aggregate Roots retenus, candidats et refusés.
- Définition des responsabilités, propriétaires, invariants, frontières, tailles et cycles de vie des vingt Roots retenues.
- Formalisation des Entités internes, Value Objects, références par identité, compositions et dépendances interdites.
- Définition des cohérences immédiate et différée, transactions métier conceptuelles et événements inter-domaines.
- Justification détaillée de la séparation entre Annonce, Cycle de vie, Modération, SEO et Recherche.
- Confirmation du caractère temporaire de Migration Legacy et définition de ses conditions de clôture.
- Documentation des critères de fusion et division futures, risques, anti-patterns, critères d’acceptation et questions ouvertes.

### Contraintes respectées

- Livrable exclusivement conceptuel et documentaire.
- Aucun code ni élément de réalisation créé.
- Aucun document normatif existant modifié.
- Seuls le nouveau document Aggregate Boundaries et le présent changelog ont été modifiés.

## [Sprint 4 — Document 2 : Domain Mapping] — 2026-07-16

### Ajouté

- Création de `docs/architecture/DOMAIN-MAPPING.md`.
- Cartographie conceptuelle des treize domaines, de leurs responsabilités, propriétaires, concepts et invariants.
- Identification des candidats Aggregate Roots, Entités et Value Objects sans structure physique.
- Définition des événements, commandes, requêtes, politiques et interactions en langage métier.
- Formalisation des dépendances autorisées et interdites, des frontières de cohérence et de l’ownership unique.
- Création du langage partagé et de la correspondance avec les documents normatifs.
- Documentation des frontières fragiles, risques de duplication, critères d’acceptation et questions ouvertes.

### Contraintes respectées

- Livrable exclusivement conceptuel et documentaire.
- Aucun code ni élément de réalisation créé.
- Aucun document normatif existant modifié.
- Seuls le nouveau Domain Mapping et le présent changelog ont été modifiés.

## [Sprint 4 — Document 1 : Architecture Blueprint] — 2026-07-16

### Ajouté

- Création de `docs/architecture/ARCHITECTURE-BLUEPRINT.md`.
- Comparaison des styles architecturaux réalistes et recommandation d'un monolithe modulaire orienté domaines avec principes de ports et adaptateurs.
- Définition de treize domaines fonctionnels, de leurs responsabilités, dépendances, priorités et statuts.
- Formalisation des frontières de modules, couches logiques et modèles d'interaction.
- Traduction architecturale du cycle de vie des annonces, de la politique média, des permissions et du SEO.
- Séparation des modèles de lecture, de l'administration et de la migration Legacy.
- Définition des principes de sécurité, performance, observabilité, audit et tests.
- Documentation des risques architecturaux et des décisions techniques encore ouvertes.

### Contraintes respectées

- Aucun code applicatif créé.
- Aucun projet Laravel créé.
- Aucune commande Composer lancée.
- Aucun modèle Eloquent, migration SQL, API détaillée ou contrôleur créé.
- Aucun fichier de configuration technique créé.
- Aucun document normatif existant modifié.

## [Sprint 3 — Document 5 : Migration Rules] — 2026-07-16

### Ajouté

- Création de `docs/MIGRATION-RULES.md`.
- Définition des principes, sources de vérité, règles de qualification, nettoyage et déduplication.
- Matrices Conserver, Nettoyer, Fusionner, Archiver et Supprimer pour chaque domaine métier.
- Formalisation des règles propres aux comptes, professionnels, annonces, médias, géographie, SEO, paiements, favoris, signalements, contenus et paramètres métier.
- Définition des données non migrées, archivées et supprimées.
- Encadrement du rapprochement des volumes, des validations métier et de la gestion des erreurs.
- Définition du plan de migration, du plan de retour et des critères d'acceptation.
- Création du cadre officiel du registre des décisions de migration.

### Contraintes respectées

- Document exclusivement métier.
- Chaque disposition est justifiée par domaine.
- Aucun code applicatif ni mécanisme de réalisation créé.

## [Sprint 3 — Document 4 : SEO Policy] — 2026-07-16

### Ajouté

- Création de `docs/SEO-POLICY.md`.
- Définition des objectifs, principes directeurs et règles de gouvernance SEO.
- Formalisation des politiques propres aux neuf types de pages publiques.
- Définition des conditions d'indexation, de non-indexation, de canonical et de redirection.
- Reconnaissance des URL historiques comme patrimoine soumis à un registre de décisions.
- Définition du traitement SEO de chaque état d'annonce.
- Encadrement du maillage, des fils d'Ariane, des données structurées, des sitemaps, de la politique Robots, de la pagination et des facettes.
- Définition des règles relatives aux soft-404, pages pauvres, duplications, qualité minimale et migration.
- Documentation des critères d'acceptation et questions ouvertes.

### Contraintes respectées

- Document exclusivement métier et fonctionnel.
- Le référencement reste subordonné aux règles métier.
- Aucune annonce non publiée ne peut être indexée.
- Aucun code applicatif ni mécanisme de réalisation créé.

## [Sprint 3 — Document 3 : Permissions Matrix] — 2026-07-16

### Ajouté

- Création de `docs/PERMISSIONS-MATRIX.md`.
- Définition des neuf acteurs officiels, de leurs responsabilités et de leurs interdictions.
- Matrice complète des dix actions métier pour quatorze ressources.
- Formalisation des permissions spéciales, séparations de responsabilités et validations à quatre yeux.
- Définition des actions obligatoirement journalisées et des informations de traçabilité attendues.
- Documentation des actions interdites, cas exceptionnels, critères d'acceptation et questions ouvertes.

### Contraintes respectées

- Document exclusivement métier.
- Permissions définies par action et par ressource, jamais par écran.
- Aucun code applicatif créé.
- Aucun mécanisme de réalisation défini.

## [Sprint 3 — Document 2 : Media Policy] — 2026-07-16

### Ajouté

- Création de `docs/MEDIA-POLICY.md`.
- Définition des médias autorisés, des images obligatoires et des règles d'image principale et d'ordre.
- Formalisation des exigences de format, qualité, dimensions, rotation, compression et variantes fonctionnelles.
- Définition des règles relatives aux contenus interdits, doublons, médias orphelins, métadonnées et droits d'utilisation.
- Encadrement des suppressions, remplacements, archives et durées de conservation.
- Définition des politiques propres aux professionnels, contenus éditoriaux, référencement, accessibilité et modération.
- Documentation des cas particuliers, critères d'acceptation et questions ouvertes.

### Contraintes respectées

- Document exclusivement métier et fonctionnel.
- Aucun code applicatif créé.
- Aucun mécanisme de réalisation défini.

## [Sprint 3 — Document 1 : Listing Lifecycle] — 2026-07-16

### Ajouté

- Création de `docs/LISTING-LIFECYCLE.md`.
- Définition des dix états officiels d'une annonce et de leurs transitions autorisées.
- Attribution des responsabilités entre annonceur, modérateur, super administrateur, commercial, responsable SEO et système.
- Formalisation des déclencheurs, notifications et effets sur le SEO, la recherche, l'administration et les statistiques.
- Définition des règles d'annulation, d'archivage, d'expiration et de renouvellement.
- Documentation des cas exceptionnels, critères d'acceptation et ambiguïtés restant à arbitrer.

### Contraintes respectées

- Document exclusivement métier.
- Aucun code applicatif créé.
- Aucun framework choisi ou créé.
- Aucune base de données créée ou définie.
- Aucune API créée ou définie.

## [Sprint 2 — Master Blueprint] — 2026-07-16

### Ajouté

- Création de `docs/MASTER-BLUEPRINT.md`.
- Définition de la vision, des objectifs et de l'architecture fonctionnelle du nouveau APPART.SN.
- Cartographie des modules obligatoires, conditionnels et exclus.
- Définition des navigations publique, utilisateur et administrative.
- Formalisation des orientations SEO, sécurité, performance et migration.
- Proposition d'une roadmap, de critères d'acceptation, d'un registre de risques et des questions ouvertes.

### Contraintes respectées

- Aucun code applicatif créé.
- Aucun projet Laravel ou autre framework créé.
- Aucune base de données créée.
- Aucune API définie ou créée.
- Aucune reprise de l'architecture technique Legacy.
## [Phase 5.3L — Final Certification & Freeze] — 2026-07-30

- 5.3A à 5.3K enregistrés GO CERTIFIÉS et FERMÉS.
- 5.3J HTTP & Security et 5.3K Operational & Audit ont levé leurs NO GO
  historiques après certification des frontières owner et Audit requises.
- `A-5.3-MODERATION-QUEUE-OWNER-READ-SOURCE-01`,
  `A-5.3-MODERATION-HTTP-READ-BOUNDARIES-01`,
  `A-5.3-AUDIT-APPEND-BOUNDARY-01`,
  `A-5.3-MODERATION-OPERATIONAL-AUDIT-EVENT-ROUTING-COVERAGE-01` et
  `A-5.3-MODERATION-RESIDUAL-OPERATIONAL-AUDIT-COVERAGE-01` sont GO CERTIFIÉS
  et FERMÉS.
- 5.3L est GO CERTIFIÉE et OUVERTE pour consolidation, campagnes terminales et
  préparation du gel. Aucun freeze final n'est encore prononcé.
- Les anciennes mentions NO GO restent des preuves historiques.
