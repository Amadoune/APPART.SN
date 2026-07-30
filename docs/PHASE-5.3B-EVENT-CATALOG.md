# Phase 5.3B — Candidate Event Catalog V1

## Statut

Catalogue **documentaire candidat uniquement**. Aucun Event concret, transport,
mapper, sérialiseur, routing, consumer, Delivery ou Outbox n'est créé.

## Catalogue fermé candidat

| Type V1 | Event Owner | Déclencheur | Consumers démontrés candidats |
|---|---|---|---|
| `moderation.report.submitted.v1` | ModerationReports | rapport appliqué | queue, métriques |
| `moderation.report.validated.v1` | ModerationReports | qualification appliquée | queue, audit |
| `moderation.decision.issued.v1` | ModerationReports | décision appliquée | audit, handoff |
| `moderation.case.closed.v1` | ModerationReports | clôture appliquée | audit, projections |
| `moderation.target-action.completed.v1` | ModerationReports | résultat cible intégré | audit, queue |

Un Event sans consumer démontré lors de 5.3G sera retiré avant implémentation.

## Identité et métadonnées

Chaque futur Event V1 devra porter :

- `eventId` déterministe ;
- type exact et version `v1` ;
- `caseId` opaque ;
- aggregate version strictement positive ;
- `occurredAt` et `recordedAt` explicites ;
- `correlationId` et `causationId` opaques ;
- `policyVersion` ;
- checksum canonique.

## Payload maximal

### Report submitted

- `caseId`, `reportId` ;
- type et identifiant opaque de cible ;
- catégorie fermée ;
- version et horodatages.

### Report validated

- `caseId`, `reportId` ;
- disposition fermée ;
- version de politique et Aggregate version.

### Decision issued

- `caseId`, `decisionId` ;
- disposition et action cible fermées ;
- `supersededDecisionId` éventuel ;
- version de politique et Aggregate version.

### Case closed

- `caseId`, code de clôture fermé ;
- décision courante opaque ;
- version de politique et Aggregate version.

### Target action completed

- `caseId`, `decisionId`, `targetActionId` ;
- résultat cible fermé ;
- target owner et contract version ;
- Aggregate version.

## Données interdites

- texte du rapport, constat ou justification ;
- identité publique du reporter, validateur, enquêteur ou décideur ;
- email, téléphone, nom, adresse ou IP brute ;
- contenu ou URI de preuve ;
- session, cookie, token ou credential ;
- SQLSTATE, stack trace ou diagnostic fournisseur ;
- snapshot d'Aggregate ;
- rôle ou permission interne.

## Compatibilité

- un type V1 garde une sémantique immuable ;
- eventId et checksum identiques convergent ;
- eventId identique et checksum différent produit une divergence ;
- type ou version inconnue est rejeté fail-closed ;
- ordre traité par Aggregate version ;
- gap, doublon et événement hors ordre ne produisent aucune mutation partielle ;
- replay et retry doivent rester idempotents ;
- aucune consommation ne mute directement un autre domaine.

## Événements historiques

Les classes historiques `ReportCreated`, `ReportValidated`, `FindingRecorded`,
`DecisionIssued` et `ModerationCaseClosed` ne sont pas déclarées contrats V1 par
ce document. Leur réconciliation avec ce catalogue devra être décidée avant
5.3G. Aucun doublon sémantique ne sera autorisé.
