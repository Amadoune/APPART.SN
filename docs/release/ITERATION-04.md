# RC2 Stabilization — Iteration 04

## Périmètre

Cette itération audite exclusivement `Claimed → BeginPublicationReviewV1 → ListingPublicationCommandGatewayV1 → Workflow/Aggregate → UnderReview`.

Approve, Projection, Search et Public Listing n'ont pas été analysés.

## Première divergence

Sur la candidature réelle de l'itération 03, `BeginPublicationReviewV1` retourne le résultat fermé `StateConflict`, avec une Queue toujours en version 2.

La lecture autoritative de la paire d'états établit :

| Modèle | État | Version |
|---|---|---:|
| `ListingPublicationWorkflow` | `submitted` | 2 |
| Aggregate `Listing` | `draft` | 0 |

La Gateway exige légitimement la paire `Workflow Submitted + Aggregate Submitted` avant BeginReview. Elle refuse donc la transition avant `SendToReview`; aucun état UnderReview n'est produit.

## Qualification

La divergence est antérieure à BeginReview : le Submit public certifié par les itérations précédentes a fait progresser le Workflow et émis `ListingSubmitted`, mais n'a pas synchronisé l'Aggregate Listing vers `Submitted`.

Une correction locale dans BeginReview devrait déduire ou rejouer Submit depuis une intention de revue. Cela introduirait une décision implicite, masquerait l'incohérence d'état et dépasserait le périmètre autorisé.

## Décision fail-closed

Aucune correction produit n'est appliquée. La Gateway, le Workflow, les ledgers et PublicationReview restent inchangés.

La cause unique restante est : **synchronisation Aggregate manquante lors du Submit public**.

## Verdict

**NO GO PROPOSÉ — ITERATION 04**
