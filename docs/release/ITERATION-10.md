# RC2 Stabilization — Iteration 10

## Périmètre

Cette itération audite exclusivement `Published → ProjectPublishedListingV1 → Projection Activation → Projection Store`.

Search et Public Listing ne sont pas interrogés.

## Preuve de départ

Listing réel issu du rejeu certifié de l'itération 09 : `add18bba-6635-4bda-aba9-e66d7bf4084e`.

L'état autoritatif observé est :

| Frontière | État |
|---|---|
| Listing Workflow / Aggregate | `Published` |
| PublicationReview Queue | `completed`, version 4 |
| Ledger Claim / Begin / Approve | trois entrées `applied` |
| Ledger Projection | absent |
| Projection Store | aucune ligne pour le Listing |

Le HTTP 200 de la vue de confirmation ne constitue donc pas une preuve de Projection : le statut `NotReady` est rendu par cette vue sans matérialisation du read model.

## Première divergence

L'inspection read-only de `CertifiedPublicListingProjectionSource` retourne :

`ProjectionSourceAssemblyStatus::PropertyMissing`.

La source certifiée exige un Aggregate `RealEstateCatalog\Property` public. Le parcours propriétaire persiste uniquement un `PropertyAuthoringState`. Aucun handoff certifié ne transforme actuellement cet état Authoring en Property public avant l'activation de Projection.

## Décision fail-closed

Aucune correction n'est appliquée dans cette itération.

Les options suivantes seraient hors périmètre ou créeraient une nouvelle décision d'architecture :

- construire artificiellement un Aggregate Property dans Projection ;
- déduire un Property public depuis `PropertyAuthoringState` ;
- relire Authoring comme source publique durable ;
- modifier Property, Submit ou Approve pour créer implicitement le handoff.

La correction nécessite d'abord une autorité owner-scoped qualifiant la promotion `Property Authoring → Property public`. Cette frontière n'existe pas dans les capacités autorisées de l'itération 10.

## Replay

Le replay Projection n'est pas atteint : aucune activation initiale n'a été appliquée et aucun ledger Projection n'existe. Search et Public Listing ne sont pas analysés.

## Cause racine unique

**Absence d'un handoff certifié transformant le Property Authoring réel en source Property publique exigée par la Projection.**

## Verdict

**NO GO PROPOSÉ — ITERATION 10**
