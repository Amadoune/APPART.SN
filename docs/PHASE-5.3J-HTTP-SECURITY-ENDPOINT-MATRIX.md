# Phase 5.3J — HTTP & Security Endpoint Matrix

## Statut

Matrice candidate documentaire. Aucun endpoint n'est créé ou ouvert.

| Surface | Méthode et chemin candidats | Contrat Application | Mutation | Préconditions | Faisabilité |
|---|---|---|---:|---|---|
| reporter | `POST /api/moderation/reports` | `SubmitModerationReportV1` | oui | session IAM, cible Listing seulement, Idempotency-Key | compatible |
| reporter | `GET /api/moderation/reports/{reportId}` | `ReadOwnModerationReportV1` | non | session IAM, auto-scope, anti-énumération | **bloquée : Query non exécutable** |
| modération | `GET /api/moderation/queue` | `ReadModerationQueueV1` | non | IAM `Investigate`, filtres fermés, curseur opaque | **bloquée : Query non exécutable** |
| modération | `POST /api/moderation/queue/{queueItemId}/claim` | `ClaimModerationQueueItemV1` | oui | IAM `Investigate`, Idempotency-Key, lease bornée | compatible |
| modération | `GET /api/moderation/cases/{caseId}` | `ReadModerationCaseV1` | non | IAM `Investigate`, vue filtrée | **bloquée : Query non exécutable** |
| modération | `POST /api/moderation/cases/{caseId}/reports/{reportId}/validation` | `ValidateModerationReportV1` | oui | IAM `Validate`, Idempotency-Key, expectedVersion | compatible |
| modération | `POST /api/moderation/cases/{caseId}/findings` | `RecordModerationFindingV1` | oui | IAM `Investigate`, Idempotency-Key, expectedVersion | compatible |
| modération | `POST /api/moderation/cases/{caseId}/decisions` | `IssueModerationDecisionV1` | oui | IAM `Decide`, Idempotency-Key, expectedVersion | compatible |
| modération | `GET /api/moderation/cases/{caseId}/decisions/{decisionId}` | `ReadModerationDecisionV1` | non | IAM `Decide` ou `Audit`, purpose fermé | **bloquée : Query non exécutable** |
| modération | `POST /api/moderation/cases/{caseId}/close` | `CloseModerationCaseV1` | oui | IAM `Decide`, Idempotency-Key, expectedVersion | compatible |

## Règles communes

- tous les chemins restent sous session IAM ;
- aucun identifiant d'acteur n'est accepté dans le JSON ;
- aucune capacité ou rôle IAM n'est accepté dans le JSON ;
- toutes les mutations exigent un `Idempotency-Key` UUID ;
- les champs inconnus sont refusés ;
- les lectures n'acceptent jamais d'Idempotency-Key comme identité ;
- `expectedVersion` est obligatoire pour toute mutation d'un dossier existant ;
- ETag peut refléter une version retournée par une Query certifiée, mais ne peut
  être inventé avant l'existence de cette Query ;
- toutes les réponses portent `Cache-Control: no-store` et
  `X-Content-Type-Options: nosniff`.

## Conclusion

La surface ne peut pas être ouverte partiellement : les lectures sont
nécessaires au parcours public et au travail privé. Toute route reste NON
OUVERTE jusqu'à certification des Queries exécutables.
