# Phase 5.3B — Commands and Queries V1

## Commands

### SubmitModerationReportV1

Crée ou rattache un rapport à un dossier propriétaire.

Entrée fermée :

- `intentId` ;
- `reportId` ;
- `actorAccountId` ;
- `target` (`ModerationTargetReferenceV1`) ;
- `category` appartenant au catalogue V1 ;
- `statementReference` opaque ;
- `occurredAt` ;
- `policyVersion`.

Le contrat n'accepte ni `caseId` choisi par le client, ni preuve brute, ni état
supposé de la cible.

### ValidateModerationReportV1

Qualifie un rapport existant.

Entrée fermée :

- `intentId`, `caseId`, `reportId` ;
- `actorAccountId` ;
- `disposition` : `Accepted`, `Rejected` ou `NeedsInformation` ;
- `reasonCode` fermé ;
- `expectedVersion`, `occurredAt`, `policyVersion`.

### RecordModerationFindingV1

Ajoute un constat append-only fondé sur un rapport accepté.

Entrée fermée :

- `intentId`, `caseId`, `findingId` ;
- `actorAccountId` ;
- liste non vide de `reportIds` ;
- `findingCode` fermé ;
- références opaques de preuves ;
- `expectedVersion`, `occurredAt`, `policyVersion`.

### IssueModerationDecisionV1

Émet ou supersède explicitement la décision courante.

Entrée fermée :

- `intentId`, `caseId`, `decisionId` ;
- `actorAccountId` ;
- liste non vide de `findingIds` ;
- `disposition` fermée ;
- action cible candidate fermée ;
- `supersededDecisionId` optionnel ;
- `expectedVersion`, `occurredAt`, `policyVersion`.

### CloseModerationCaseV1

Clôture un dossier lorsque sa politique de clôture est satisfaite.

Entrée fermée :

- `intentId`, `caseId`, `actorAccountId` ;
- `closureCode` fermé ;
- `expectedVersion`, `occurredAt`, `policyVersion`.

### ClaimModerationQueueItemV1

Réserve temporairement un item de travail.

Entrée fermée :

- `intentId`, `queueItemId`, `actorAccountId` ;
- `leaseId` ;
- `leaseExpiresAt` ;
- `occurredAt`, `policyVersion`.

Le lease ne transfère aucune autorité métier.

## Queries

### ReadOwnModerationReportV1

Entrée :

- `reportId` ;
- `actorAccountId`.

Sortie minimale :

- statut public agrégé ;
- dernier horodatage publiable ;
- indication fermée d'action attendue du reporter.

Sont exclus : identité des modérateurs, constats, décision interne, preuve,
target state, diagnostics et historique privé.

### ReadModerationQueueV1

Entrée :

- `actorAccountId` auto-scopé ;
- filtres fermés de priorité, catégorie et âge ;
- curseur opaque ;
- limite bornée.

Sortie :

- items minimaux de travail ;
- lease state publiable au modérateur ;
- curseur suivant opaque ;
- checkpoint de projection opaque.

### ReadModerationCaseV1

Entrée :

- `caseId` ;
- `actorAccountId` auto-scopé ;
- niveau de vue fermé issu de l'autorisation.

Sortie privée filtrée :

- cible opaque ;
- version du dossier ;
- rapports et qualifications autorisés ;
- constats et décisions autorisés ;
- références opaques de preuves ;
- état de handoff sans diagnostic technique.

### ReadModerationDecisionV1

Entrée :

- `caseId` ou `decisionId` ;
- `actorAccountId` auto-scopé ;
- purpose fermé.

Sortie :

- décision courante ou décision identifiée ;
- disposition et action cible fermées ;
- version de politique ;
- état d'application ;
- identifiants opaques nécessaires à l'audit.

## Règles de lecture

- toutes les Queries sont read-only ;
- aucune Query ne retourne un Aggregate ou un snapshot de Persistence ;
- `Missing`, `NotVisible`, `Corrupted` et `DependencyUnavailable` restent des
  résultats distincts en interne ;
- la couche publique peut homogénéiser les résultats pour empêcher
  l'énumération ;
- aucune Query n'effectue de reconstruction depuis des Events externes.
