# Phase 4.7 — Administrative Action Lifecycle HTTP Endpoint Specification

## Requête

Champs communs obligatoires :

- `action` : `record`, `approve` ou `reject` ;
- `contextVersion` : `1` ;
- `expectedVersion` : entier strictement positif ;
- `actorId` ;
- `occurredAt` et `recordedAt` au format UTC microseconde ;
- `historicalReason` : 10 à 1 000 caractères ;
- `reasonEvidence` : `present` ou `missing` ;
- `recordingDisposition` ;
- `authorId` et `decisionActorId`.

Champs conditionnels :

| Action | Disposition | `approvalId` | `decisionId` | Acteur |
|---|---|---|---|---|
| `record` | direct ou independent | interdit | interdit | auteur |
| `approve` | independent uniquement | UUID obligatoire | UUID obligatoire | décideur |
| `reject` | independent uniquement | interdit | UUID obligatoire | décideur |

Pour `direct_recording`, auteur et décideur sont identiques. Pour
`independent_approval_required`, ils sont distincts. Ces contrôles sont des
contraintes de forme du contrat reçu, non une décision métier HTTP.

## Réponse

```json
{
  "status": "resultat_applicatif",
  "diagnostic": null
}
```

Un diagnostic Workflow est restitué sans traduction ni réinterprétation.

## Erreurs de transport

Une route non UUID retourne 404. Une charge absente, incohérente ou hors du
catalogue fermé retourne 422 avant toute orchestration.
