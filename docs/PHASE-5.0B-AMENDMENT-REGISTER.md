# Phase 5.0B — Amendment Register

## 1. Amendements certifiés et intégrés

| ID | Cible | Décision | Documents principaux | Statut |
|---|---|---|---|---|
| A-4.7-R1 | Administrative Action transition context | contexte/replay explicite | `PHASE-4.7-ADMINISTRATIVE-ACTION-TRANSITION-CONTEXT-CERTIFICATION.md` | certifié, gelé |
| A-4.7-R2 | Administrative Action historical mirror | mutation/enrollment canonicalization | `PHASE-4.7B-R2-CERTIFICATION.md` | certifié, gelé |
| A-4.8-R1 | Place merge context | contexte de fusion V1 | `PHASE-4.8A-R1-PLACE-MERGE-CONTEXT-CERTIFICATION.md` | certifié, gelé |
| A-4.8-R2 | Place workflow decision boundary | séparation de décision | `PHASE-4.8A-R2-WORKFLOW-DECISION-BOUNDARY-AMENDMENT.md` | certifié, gelé |
| A-4.8-R3 | Place replay target evidence | preuve de cible | `PHASE-4.8A-R3-REPLAY-INSPECTION-TARGET-EVIDENCE-AMENDMENT.md` | certifié, gelé |
| A-4.8-R4 | Place replay attempt identity | identité de tentative | `PHASE-4.8A-R4-REPLAY-ATTEMPT-IDENTITY-AMENDMENT.md` | certifié, gelé |
| A-4.8J-R2 | Generic Delivery contract | compatibilité multi-owner | `PHASE-4.8J-R2-GENERIC-DELIVERY-CONTRACT-COMPATIBILITY-AMENDMENT.md` | certifié, gelé |
| A-4.8J-R3 | Generic Delivery output | type de sortie explicite | `PHASE-4.8J-R3-GENERIC-DELIVERY-OUTPUT-TYPE-AMENDMENT.md` | certifié, gelé |
| A-PRO-REPLAY | Professional Status replay policy | politique de replay | `PROFESSIONAL-STATUS-REPLAY-POLICY-CONTRACT-AMENDMENT.md` | certifié, gelé |
| A-RSV-ROUTER | Reservation routing outcome/port | résultat de routing explicite | dossiers `RESERVATION-LIFECYCLE-*-AMENDMENT-*` | intégré et certifié avec lifecycle |
| A-4.9-R1 | Account Status decision boundary | autorité de décision | `PHASE-4.9A-R1-ACCOUNT-STATUS-DECISION-BOUNDARY-AMENDMENT.md` | certifié, gelé |
| A-4.9-R2 | Account Registry runtime source | source de production | `PHASE-4.9C-R2-ACCOUNT-REGISTRY-RUNTIME-SOURCE-AMENDMENT.md` | certifié via piste corrective |
| A-4.9-R3 | Historical Account source resolution | résolution de source | `PHASE-4.9C-R3-HISTORICAL-ACCOUNT-SOURCE-RESOLUTION-AMENDMENT.md` | certifié, gelé |
| A-4.9J-R2 | Account consumer compatibility | compatibilité du consumer générique | `ACCOUNT-STATUS-CONSUMER-COMPATIBILITY-AMENDMENT.md` et certification R2 | certifié, gelé |
| A-4.9J-R3 | Account routed delivery boundary | frontière de livraison | `PHASE-4.9J-R3-ROUTED-DELIVERY-BOUNDARY-CERTIFICATION.md` | certifié, gelé |
| A-5.1-IAM-PROFILE-01 | User Profile / Identity Claims | extension additive Profile et Claims | dossiers `PHASE-5.1-IAM-PROFILE-*` | GO CERTIFIÉ, FERMÉ |
| A-5.1-IAM-CLOSURE-01 | Account Closure | Closed/Reopened distinct de Suspended | dossiers `PHASE-5.1-IAM-CLOSURE-*` | GO CERTIFIÉ, FERMÉ |
| A-5.1-IAM-PERSISTENCE-BOUNDARY-01 | frontière Application/Persistence | suppression de l'inversion Application → Infrastructure | dossiers `A-5.1-IAM-PERSISTENCE-BOUNDARY-01-*` | GO CERTIFIÉ, FERMÉ |
| A-5.2A-LISTING-CREATION-BOUNDARY-01 | frontière Listing/F-01 | frontière publique `CreateListingDraftV1` additive | dossiers `A-5.2A-LISTING-CREATION-BOUNDARY-01-*` | GO CERTIFIÉ, FERMÉ |
| A-5.3-ROADMAP-SEQUENCING-ALIGNMENT-01 | séquence terminale Phase 5.3 | conservation de 5.3I Command Handoff Listing et alignement prospectif 5.3J/5.3K/5.3L | dossiers `A-5.3-ROADMAP-SEQUENCING-ALIGNMENT-01*` | GO CERTIFIÉ, FERMÉ |
| A-5.3-BASELINE-IMPORT-WHITESPACE-QUALIFICATION-01 | première matérialisation Git de la baseline certifiée | qualification du retrait strict d'un LF terminal dans 220 Markdown et 19 SQL gelés | dossiers `A-5.3-BASELINE-IMPORT-WHITESPACE-QUALIFICATION-01*` | GO CERTIFIÉ, FERMÉ |
| A-5.3-BASELINE-IMPORT-WHITESPACE-NORMALIZATION-01 | première matérialisation Git de la baseline certifiée | retrait certifié d'un LF terminal dans 220 Markdown et 19 SQL gelés, puis recertification Architecture/PostgreSQL | `A-5.3-BASELINE-IMPORT-WHITESPACE-NORMALIZATION-01-CERTIFICATION.md` | GO CERTIFIÉ, FERMÉ |

Les rapports de suspension et NO GO sont des preuves historiques, pas des
amendements ouverts. Leur résolution certifiée est enregistrée ci-dessus.

## 2. Amendements ouverts

| ID | Cible | Objet | Statut |
|---|---|---|---|

Aucun amendement n'est ouvert.

## 3. Amendements identifiés et décisions

| ID réservé | Cible | Objet minimal | Statut |
|---|---|---|---|
| A-5.1-IAM-01 | Account/AccountRegistry et frontières 4.9 | audit des dix fonctions IAM | NO GO CERTIFIÉ, FERMÉ |
| A-5.1-IAM-PROFILE-01 | identité principale Account/Profile | modèle UserProfile + Identity Claim additif | GO CERTIFIÉ, FERMÉ |
| A-5.1-IAM-CLOSURE-01 | cycle de vie Account | modèle Account Closure additif | GO CERTIFIÉ, FERMÉ |
| A-5.1-IAM-PERSISTENCE-BOUNDARY-01 | persistance 5.1C/5.1D | correction architecturale sans évolution métier | GO CERTIFIÉ, FERMÉ |
| A-5.1-IAM-ERASURE-01 | Historical Account et PII cross-domain | anonymisation/effacement irréversibles après rétention | identifié, non ouvert |
| A-5.1-IAM-OUTBOX-CONCURRENCY-01 | Outbox IAM 5.1H | convergence atomique des conflits simultanés message/event | GO CERTIFIÉ, FERMÉ |
| A-5.2A-LISTING-CREATION-BOUNDARY-01 | frontière Listing/F-01 | exposer une création Listing publique, fermée et idempotente sans modifier la sémantique lifecycle | GO CERTIFIÉ, FERMÉ |
| A-5.2B-MEDIA-ATTACHMENT-BOUNDARY-01 | frontière Media Ingestion/F-06 | exposer un rattachement public, fermé et idempotent d’un asset sûr sans modifier MediaCollection ni Media Item Lifecycle | GO CERTIFIÉ, FERMÉ |
| A-5.2C-PROFESSIONAL-STATUS-READ-BOUNDARY-01 | frontière Professional Profile/F-05 | auditer la lecture publique existante | NO GO CERTIFIÉ, FERMÉ |
| A-5.2C-PROFESSIONAL-STATUS-PUBLIC-READ-01 | frontière publique F-05 | définir `ProfessionalPublicStatusReaderV1` et son résultat fermé | GO CERTIFIÉ, FERMÉ |
| A-5.2C-PROFESSIONAL-MANDATE-RESOLUTION-01 | frontière Account/Professional Core | auditer la résolution AccountId vers ProfessionalId | NO GO CERTIFIÉ, FERMÉ |
| A-5.2C-PROFESSIONAL-MANDATE-PUBLIC-RESOLUTION-01 | frontière Account/Professional Core | définir `ProfessionalMandateResolverV1` et son résultat fermé | GO CERTIFIÉ, FERMÉ |
| A-5.3-ROADMAP-SEQUENCING-ALIGNMENT-01 | séquence terminale Phase 5.3 | alignement documentaire prospectif sans réécriture historique | GO CERTIFIÉ, FERMÉ |
| A-5.3-MODERATION-HTTP-READ-BOUNDARIES-01 | Queries HTTP owner ModerationReports | ports, résultats, readers et bindings des quatre lectures V1 ; NO GO historique levé après certification des sources owner | GO CERTIFIÉ, FERMÉ |
| A-5.3-MODERATION-REPORT-OWNER-READ-SOURCE-01 | source owner Report | résolution durable et read-only de reportId sans exposer le snapshot | GO CERTIFIÉ, FERMÉ, GELÉ |
| A-5.3-MODERATION-QUEUE-OWNER-READ-SOURCE-01 | source owner Queue | collection filtrée, curseur opaque et limite bornée | GO CERTIFIÉ, FERMÉ |
| A-5.3-MODERATION-QUEUE-IDEMPOTENCE-01 | Queue ModerationReports | journal d'intents durable, claims atomiques et convergence | GO CERTIFIÉ, FERMÉ |
| A-5.3-MODERATION-ATOMIC-OUTBOX-BOUNDARY-01 | transaction owner ModerationReports | frontière Application atomique mutation–Event–Outbox | GO CERTIFIÉ, FERMÉ |
| A-5.3-IAM-MODERATOR-AUTHORIZATION-01 | frontière IAM Moderation | contrat public read-only des cinq capacités | GO CERTIFIÉ, FERMÉ |
| A-5.3-IAM-MODERATOR-AUTHORIZATION-IMPLEMENTATION-01 | implémentation IAM Moderation | reader owner IAM et binding singleton | GO CERTIFIÉ, FERMÉ |
| A-5.3-LISTING-MODERATION-BOUNDARY-01 | frontière Listing Moderation | Reader et Command Gateway V1 | GO CERTIFIÉ, FERMÉ |
| A-5.3-LISTING-MODERATION-INTENT-SOURCE-01 | intents Listing Moderation | idempotence durable commandId/checksum | GO CERTIFIÉ, FERMÉ |
| A-5.3-LISTING-MODERATION-VERSION-READ-01 | version owner Listing | lecture owner-locale minimale expectedVersion | GO CERTIFIÉ, FERMÉ |
| A-5.3-LISTING-MODERATION-BOUNDARY-IMPLEMENTATION-01 | implémentation Listing Moderation | Reader/Gateway exécutables et bindings | GO CERTIFIÉ, FERMÉ |
| A-5.3-MODERATION-LISTING-HANDOFF-OUTBOX-ROUTING-01 | route Listing Handoff | destination statique et couverture Outbox | GO CERTIFIÉ, FERMÉ |
| A-5.3-AUDIT-APPEND-BOUNDARY-01 | AdministrationAudit public append | contrat et implémentation owner append-only | GO CERTIFIÉ, FERMÉ |
| A-5.3-MODERATION-OPERATIONAL-AUDIT-EVENT-ROUTING-COVERAGE-01 | quatre parcours Audit initiaux | Events Finding/Queue, production, routing et consumer Audit | GO CERTIFIÉ, FERMÉ |
| A-5.3-MODERATION-RESIDUAL-OPERATIONAL-AUDIT-COVERAGE-01 | trois parcours Audit résiduels | Decision, Close, Listing completion et Runtime Audit à sept Events | GO CERTIFIÉ, FERMÉ |
| A-5.3-GLOBAL-PROOF-QUALIFICATION | preuves globales Phase 5.3 | qualifier timeout PostgreSQL complet et écart Pint historique | APPROUVÉ, CLOS — TOUTES RÉSERVES LEVÉES |
| A-5.3-POSTGRESQL-FULL-CAMPAIGN-DIAGNOSTIC-01 | preuves globales Phase 5.3 | qualifier l'absence de verdict terminal PostgreSQL | APPROUVÉ, CLOS — 658 TESTS, 2 935 ASSERTIONS, PASS |
| A-5.3-PINT-GLOBAL-GATE-QUALIFICATION-01 | gate qualité globale et amendement 5.3G | restaurer Pint global sans changement sémantique | GO CERTIFIÉ, FERMÉ, GELÉ |

Ce registre ne préjuge pas du contenu ni du GO de l'amendement. Son ouverture
exige le processus de `PHASE-5.0B-GOVERNANCE.md`.

## 4. Champs obligatoires d'une future entrée

- identifiant stable et version ;
- capacité/version ciblée ;
- besoin et alternatives ;
- owners et consumers affectés ;
- impact contracts/events/schema/runtime/outbox/HTTP ;
- migration, rollback, replay et compatibilité ;
- campagne de recertification ;
- décisions d'ouverture, GO/NO GO et clôture ;
- liens vers preuves.
