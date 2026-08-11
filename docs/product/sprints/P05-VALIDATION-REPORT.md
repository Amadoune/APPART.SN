# P05 — Validation Report

## Capacités certifiées

La chaîne P05 est intégralement matérialisée par composition des capacités existantes :

| Capacité | Statut | Preuve |
|---|---|---|
| Login réel | PASS | Identity Access HTTP Runtime et session HTTPS réels |
| Workspace réel | PASS | accès protégé par la session IAM owner-scoped |
| Property réel | PASS | `initiate-property` exécuté terminalement |
| Listing Draft réel | PASS | `create-listing` exécuté terminalement |
| Étape Photos | PASS | branchée sur Media Authoring owner-scoped réel |
| Prévisualisation | PASS | alimentée exclusivement par les saisies et médias réellement acceptés |
| Submit | PASS | branché sur `submit-listing` et le pipeline certifié |
| Limite Submitted | PASS | aucune publication, Search ou Projection déclenchée par P05 |

Ces capacités ne reposent sur aucun mock, SQL direct, owner fourni par le client ou donnée silencieusement fabriquée.

## Gates techniques

| Gate | Résultat |
|---|---|
| Feature ciblée | PASS — 10 tests, 53 assertions |
| Architecture ciblée | PASS — 9 tests, 113 assertions |
| PHPStan ciblé | PASS — 0 erreur |
| Pint ciblé | PASS |
| Vite | PASS |
| Responsive desktop/tablette/mobile | PASS |
| Débordement horizontal | PASS — absent aux trois formats |
| Console navigateur | PASS — 0 erreur, 0 warning |
| git diff --check | PASS |

## Prérequis de démonstration

La démonstration terminale `Upload → Preview → Submit` nécessite qu’un fichier représentant un média immobilier réel soit disponible dans l’environnement local et autorisé pour cet usage.

Un tel fichier n’était pas disponible lors du rejeu. Conformément à la règle « aucune donnée fictive », aucune image artificielle, capture sans rapport avec le bien ou fixture silencieuse n’a été injectée.

Cette absence est exclusivement un **prérequis de démonstration non satisfait**. Elle ne constitue pas une défaillance de :

- Media Authoring ;
- Property Authoring ;
- IAM ;
- Listing Authoring ;
- l’assistant ou le parcours P05.

Elle empêche uniquement de produire, dans cet environnement, une nouvelle preuve visuelle terminale couvrant successivement l’upload, la prévisualisation puis le Submit.

## Hors périmètre

`BeginReview`, `ApproveAndPublish`, la publication, Search, la Projection publique et la fiche publique ne sont pas des critères P05.

## Intégrité

Aucun composant IAM, Media Runtime, Property, Listing Lifecycle, Search ou Projection n’a été modifié. Aucun staging, commit ou tag.
