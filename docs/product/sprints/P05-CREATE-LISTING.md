# P05 — Create Listing Experience (Reopening)

## Objectif

Composer les capacités certifiées IAM, Property Authoring et Media Authoring sous la forme d’un assistant propriétaire en sept étapes : projet, type, localisation, informations, photos, prévisualisation et Submit.

## Composition

| Étape UI | Surface certifiée |
|---|---|
| Connexion | Identity Access HTTP Runtime |
| Type et localisation | `initiate-property` |
| Informations | `create-listing` |
| Photos | `/api/authoring/properties/{propertyId}/media` |
| Prévisualisation | valeurs saisies + médias effectivement acceptés |
| Soumission | `submit-listing` |

Les identifiants techniques et les clés d’idempotence sont générés côté client. L’owner provient exclusivement de la session IAM. Aucun SQL, Aggregate, Runtime ou règle métier n’est ajouté.

## UX

Un écran correspond à une décision. La progression est visible, les champs sont labellisés, les erreurs sont annoncées avec `aria-live`, et la navigation Retour/Continuer fonctionne au clavier. Le responsive est qualifié à 390, 768 et 1440 pixels sans débordement horizontal.

## Limite certifiée

Le Submit réel déclenche exclusivement la transition certifiée vers `Submitted`. Il ne constitue pas une approbation ni une publication. P05 ne possède aucune autorité pour exécuter `BeginReview` ou `ApproveAndPublish` ; la recherche et la fiche publiques ne peuvent donc pas être garanties par le seul parcours propriétaire.
