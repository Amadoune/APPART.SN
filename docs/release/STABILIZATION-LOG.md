# RC2 Stabilization Log

## Iteration 11 — Final End-to-End Certification 01 — 2026-08-16

- Historical Reopening 04 source blockers: closed by certified owner materializations.
- Active Generation: Found/active, exactly one generation and one RC2 projection.
- Certified source: Found/Ready; persisted projection current and equivalent.
- Public read model: Found; public page and binary delivery HTTP 200.
- Search UX/API: not opened.
- UI `NotReady -> confirmed`: retained as non-blocking residual defect.
- First runtime divergence: NONE.
- Verdict: **GO PROPOSÉ — RC2 ITERATION 11 — GO CERTIFIÉ / FERMÉ.**

Historical stabilization entries remain below.

## Iteration 01 — Submit Listing

### Première divergence initiale

`POST /api/public-authoring/v1/submit-listing → HTTP 503`

### Cause

La création publique persistait l'Aggregate Listing, le Draft Authoring, l'ownership et le portfolio, mais n'initialisait pas le `ListingPublicationWorkflow` en état `Draft`. Lors du Submit, `ListingPublicationOrchestrator` lisait donc un workflow `Missing` et produisait `PersistenceFailure`, mécaniquement réduit en HTTP 503.

### Correction unique

`DeterministicCreateListingDraftV1` initialise désormais le workflow `Draft` par `ListingPublicationWorkflowStore` dans la transaction de création existante. Seuls `Applied` et `AlreadyApplied` convergent ; tout autre résultat provoque le rollback global.

Authoring Operations continue de dépendre uniquement de `CreateListingDraftV1` et de `ListingPublicationOrchestrator`. Aucun accès direct au store Lifecycle n'a été introduit dans cette couche.

### Rejeu

Le rejeu réel démontre :

`Login → Property → Listing → Upload → Preview → Submit → Submitted → Reviewer Login → Authorization`

Première divergence suivante : la carte de la candidature Submitted n'apparaît pas dans Publication Review Queue. Arrêt immédiat ; aucune correction Queue n'est ouverte.

## Iteration 02 — Publication Review Queue Ingestion

### Première divergence

Le Submit public utilisait le workflow direct. L'état `Submitted` était persisté sans émission du fait `ListingSubmitted`; le consumer PublicationReview n'était jamais atteint.

### Correction unique

Le Submit public délègue désormais à `ListingPublicationEventOrchestrator`. Celui-ci produit l'événement catalogué et réalise atomiquement Workflow, Outbox et ingestion Queue. La transaction événementielle reste propriétaire de cette composition et n'est plus imbriquée dans la transaction Authoring.

### Rejeu

`Owner Login → Upload → Preview → Submit HTTP 200 → Submitted → Reviewer Login → Authorization → Queue`

Listing réel : `123f9e41-1be9-419f-a92b-b247a04e8ee9`. Une carte Queue unique portant `123f9e41` est visible. Arrêt avant Claim conformément au fail-fast.

Verdict : **GO PROPOSÉ — ITERATION 02**.

## Iteration 03 — Publication Review Claim

### Première divergence

Le Claim initial était appliqué, mais son replay HTTP retournait 409. L'instant `occurredAt`, inclus dans l'identité canonique du Claim, était régénéré par le contrôleur à chaque requête. Le ledger détectait donc correctement une commande divergente.

### Correction unique

Le formulaire Claim fige et transporte `occurredAt`. La Request valide ce champ et le contrôleur le transmet sans le recréer. Le store, le ledger et l'optimistic locking restent inchangés.

### Rejeu

Listing `72b990e2-6b32-44ab-93c3-0ce688c0b18d` : Claim HTTP 200, QueueItem assigné, replay strict HTTP 200 / `AlreadyApplied`.

Arrêt avant BeginReview conformément au fail-fast.

Verdict : **GO PROPOSÉ — ITERATION 03**.

## Iteration 04 — Begin Review

### Première divergence

`BeginPublicationReviewV1` retourne `StateConflict` avant toute transition.

État autoritatif observé pour le Listing `72b990e2-6b32-44ab-93c3-0ce688c0b18d` : Workflow `submitted` version 2, Aggregate `draft` version 0.

### Décision

La Gateway refuse correctement une paire d'états incohérente. Rejouer implicitement Submit depuis BeginReview constituerait une nouvelle décision et une correction hors périmètre. Aucune modification n'est effectuée.

Cause unique restante : le Submit public ne synchronise pas l'Aggregate Listing vers `Submitted`.

Verdict : **NO GO PROPOSÉ — ITERATION 04**.

## Iteration 05 — Submit Aggregate Synchronization

### Première divergence

Le Submit public n'appelait pas `SubmitListing`; seul le Workflow atteignait `submitted`.

### Correction unique

Le Submit public compose désormais la révision déterministe, l'évidence owner-scoped et `SubmitListing` avant la transition événementielle, dans une transaction locale unique avec savepoint Aggregate+Outbox.

### Preuve

Listing `39f3e66e-822a-4a61-98bb-e763eff21acd` : Workflow `submitted` version 2 et Aggregate `submitted` version 1. Queue et Claim sont atteints.

### Première divergence suivante

`BeginReview → HTTP 503`.

Arrêt immédiat. La cause du 503 n'est pas analysée et aucune seconde correction n'est appliquée.

Verdict : **NO GO PROPOSÉ — ITERATION 05**.

## Iteration 06 — Begin Review HTTP 503

### Première cause

`SendToReview` utilisait le catalogue Property Registry historique, qui répondait `unavailable`, alors que le Property réel était `eligible` dans Property Authoring.

### Correction unique

Un binding contextuel de la Gateway fournit à `SendToReview` le `PropertyAuthoringCatalogAdapter` existant. Aucun modèle ou contrat métier n'est modifié.

### Rejeu

Listing `4f45cbb9-0475-4028-bcd5-caf4a86ca29f` : BeginReview HTTP 200, Workflow `under_review` version 3, Aggregate `under_review` version 2.

### Première divergence suivante

Le replay strict BeginReview retourne HTTP 409. Arrêt avant Approve ; aucune seconde correction n'est appliquée.

Verdict : **NO GO PROPOSÉ — ITERATION 06**.

## Iteration 07 — Begin Review Replay

### Première divergence

Le formulaire BeginReview ne transportait pas `occurredAt`, pourtant inclus dans le checksum canonique. Le contrôleur recréait cet instant au replay ; le ledger recevait le même `commandId` avec une identité différente et retournait `DivergentCommand` / HTTP 409.

### Correction unique

Le formulaire fige et transporte `occurredAt`. La Request le valide et le contrôleur le transmet sans le recréer. Ledger, checksum et règles métier restent inchangés.

### Rejeu

Listing `7ec63dab-dd11-4b2c-84a2-3ba6eb96de46` : BeginReview HTTP 200, Workflow et Aggregate `UnderReview`, replay strict HTTP 200 / `AlreadyApplied`.

Arrêt avant Approve conformément au fail-fast.

Verdict : **GO PROPOSÉ — ITERATION 07**.

## Iteration 08 — Approve Publication

### Première divergence

ApprovePublication retournait HTTP 503. `PublishListing` utilisait le catalogue Property Registry historique, qui répondait `unavailable` pour le Property Authoring réel ; l'adaptateur owner-scoped certifié répondait `eligible`.

### Correction unique

Un binding contextuel de la Gateway fournit à `PublishListing` le `PropertyAuthoringCatalogAdapter` existant. Les règles Lifecycle, Property, Media et PublicationReview restent inchangées.

### Rejeu

Listing `f2f04694-6db3-4c0b-b884-d3520556bf83` : ApprovePublication HTTP 200, Workflow `published` version 4, Aggregate `published` version 3, Queue `completed` version 4.

### Première divergence suivante

Le replay strict ApprovePublication retourne HTTP 409. Arrêt immédiat ; aucune seconde correction et aucune analyse Projection/Search.

Verdict : **NO GO PROPOSÉ — ITERATION 08**.

## Iteration 09 — Approve Publication Replay

### Première divergence

Le formulaire Approve ne transportait pas `occurredAt`, pourtant inclus dans les checksums canoniques PublicationReview, Gateway et activation. Le contrôleur recréait l'instant au replay ; le même `commandId` devenait une commande divergente et retournait HTTP 409.

### Correction unique

Le formulaire fige et transporte `occurredAt`. La Request le valide et le contrôleur le transmet sans le recréer. Les ledgers, checksums et règles métier restent inchangés.

### Rejeu

Listing `add18bba-6635-4bda-aba9-e66d7bf4084e` : ApprovePublication HTTP 200, Published, replay strict HTTP 200 / `AlreadyApplied`.

Arrêt immédiat sans qualification Projection, Search ou Public Listing.

Verdict : **GO PROPOSÉ — ITERATION 09**.

## Iteration 10 — Projection Activation

### Première divergence

Pour le Listing Published `add18bba-6635-4bda-aba9-e66d7bf4084e`, aucun ledger Projection et aucune ligne `public_projection.listing_projections` n'existent. La source certifiée retourne `PropertyMissing`.

### Cause racine unique

Le parcours possède un `PropertyAuthoringState`, mais aucune autorité certifiée ne le promeut en Aggregate `RealEstateCatalog\Property` public exigé par la Projection.

### Décision

Aucune correction : synthétiser le Property dans Projection, déduire sa publication ou relire Authoring comme source publique dépasserait le périmètre et créerait une nouvelle frontière métier.

Le replay Projection n'est pas atteint. Search et Public Listing ne sont pas analysés.

Verdict : **NO GO PROPOSÉ — ITERATION 10**.

## Iteration 11 — Published → Projection Materialization

### Reprise

L’itération 10 demeure historiquement `NO GO` sur `PropertyMissing`. F0–F7 sont désormais certifiées et fermées ; un nouveau parcours devait être créé pour tester l’état courant.

### Première divergence

Le navigateur rejoint réellement le formulaire HTTPS owner via « Déposer une annonce », mais aucune session owner n’est active et aucun credential clair autorisé n’est fourni à la campagne. La politique IAM interdit de reconstruire le secret depuis le hash, d’injecter un cookie ou de fabriquer une session.

### Arrêt fail-fast

Arrêt à `Owner Login`, avant Property, Listing, Upload et Submit. Aucun identifiant de nouveau Listing/Property/commande n’existe, car aucune mutation n’a été déclenchée. Projection et Search ne sont pas atteints.

Aucune correction produit n’est appliquée.

Verdict : **NO GO PROPOSÉ — ITERATION 11**.

### Reopening 01

Le principal Authoring est désormais certifié et n'a pas été recréé. La reprise s'arrête avant toute réponse HTTP : le navigateur contrôlable retourne `net::ERR_BLOCKED_BY_CLIENT` lors de l'ouverture de `https://appart.test/authoring/workspace`.

Première divergence unique : `Workspace HTTPS → blocage navigateur avant HTTP`. Aucun composant produit n'est atteint, aucune correction n'est appliquée, aucun identifiant de parcours n'est créé et Search reste non ouvert.

Verdict : **NO GO PROPOSÉ — ITERATION 11 — REOPENING 01**.

### Reopening 02

Deux principals locaux distincts ont été provisionnés par les chaînes IAM certifiées et le manifest `5a00c6ca-82f6-4574-aebe-821d0aa546c6` a été ouvert avant navigation.

Première divergence unique : le profil Chrome système neuf `RC2-OWNER-5a00c6ca` retourne `net::ERR_CERT_AUTHORITY_INVALID` sur le GET du formulaire Login, avant toute réponse HTTP. La campagne s'arrête avant Login ; aucun parcours Authoring ou Review n'est engagé.

Verdict : **NO GO PROPOSÉ — ITERATION 11 — REOPENING 02**.

### Reopening 03

Le préflight TLS dans Chrome système manuel est attesté PASS par l'opérateur. La campagne s'arrête ensuite au Login owner : aucune surface de saisie/observation manuelle de la fenêtre Chrome n'est exposée à l'agent, tandis que toutes les surfaces d'automatisation disponibles sont interdites.

Première divergence unique : `Owner Login manuel → surface d'interaction autorisée indisponible`. Aucun défaut produit, POST ou état applicatif n'est atteint.

Verdict : **NO GO PROPOSÉ — ITERATION 11 — REOPENING 03**.

### Reopening 04

- Campagne : `a905da4c-8d13-4a4a-9e35-e224b0b03c52`.
- Resume / Submit : PASS ; Property canonique et Promotion ledger présents ; Aggregate et Workflow `submitted` ; Queue créée.
- Claim : PASS ; replay strict `already_applied`.
- BeginReview : PASS ; Aggregate et Workflow `under_review` ; replay strict `already_applied`.
- ApprovePublication : PASS ; Aggregate `published` v3, Workflow `published` v4, Queue `completed` v4.
- Projection : FAIL-FAST ; résultat fermé `not_ready`, replay strict `not_ready`.
- Cause racine : source de Projection `search_missing` ; zéro ledger Projection, zéro projection matérialisée.
- Observation UI : la vue `confirmed` est rendue pour `NotReady`, produisant une confirmation visuelle non corroborée par les stores.
- Search : non ouvert. Correction : aucune.

Verdict : **NO GO PROPOSÉ — ITERATION 11 — REOPENING 04**.
## Post-Iteration-11 — NotReady HTTP/UI reduction

- Historical Iteration 11 verdict preserved: GO certified/closed.
- Root cause: approve adapter selected `confirmed` for every non-exception result, including `NotReady`.
- Correction: explicit `not-ready` presentation mode and non-success Blade state.
- HTTP contract remains 200 with the existing closed result; Applied/AlreadyApplied remain confirmed.
- Residual defect status: CLOSED.
- No Projection replay, RC2 data mutation, staging, commit or tag.
