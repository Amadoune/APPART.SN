# Phase 4.5 — Professional Status Lifecycle Contract Blueprint

## Workflow

Types prévus :

- `ProfessionalStatusWorkflow` ;
- `ProfessionalStatusState` : `Active`, `Suspended` ;
- `ProfessionalStatusAction` : `Suspend`, `Reactivate`, `Unknown` ;
- transition, décision, diagnostic et résultat fermés.

Matrice cible :

| État | Action | Résultat |
|---|---|---|
| Active | Suspend | Suspended |
| Suspended | Reactivate | Active |
| Active | Reactivate | refus typé |
| Suspended | Suspend | refus typé |

`initialState()` retourne `Active`. L'enregistrement demeure une entrée explicite et n'est pas transformé en transition implicite.

## Persistance et orchestration

Un journal append-only propriétaire matérialisera exclusivement les deux transitions certifiées. Version, contexte (`actor`, `occurredAt`) et politique de rejeu devront être contractuels avant toute orchestration. Le workflow restera l'unique propriétaire des décisions.

## Événements

Le catalogue de statut contiendra exclusivement les faits correspondant aux transitions :

- `professional.status.suspended` ;
- `professional.status.reactivated`.

`ProfessionalRegistered` n'est pas produit par une initialisation de store. Les payloads seront minimaux : aucune raison libre, donnée d'établissement, mandat, représentant ou numéro d'enregistrement.

## Transport et routage

Le transport sera opaque, byte-for-byte et séparera `eventId` de `messageId`. Le port de routage retournera un résultat fermé dès sa première version. Aucun port `void` n'est autorisé.

## Consommation et Outbox

La politique d'acquittement sera certifiée avant le Consumer. L'owner additif sera `Professionals → professionals`. Le Consumer et les inscriptions Worker ne seront introduits qu'après certification de la destination durable et de la composition Runtime.
