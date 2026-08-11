# P05 — Implementation Evidence

## Éléments livrés

- Workspace remplacé par un assistant produit en sept étapes.
- Persistance Property réelle avant l’étape Informations.
- Création du brouillon Listing réel avant l’étape Photos.
- Upload multipart réel vers Media Authoring, sans owner fourni par le client.
- Prévisualisation issue exclusivement des saisies et fichiers acceptés.
- Submit relié à `submit-listing` avec version optimiste et idempotence.
- Logout IAM réel conservé dans le workspace.

## Démonstration locale

Le principal F3 s’est connecté sur HTTPS. Le parcours a créé, par les routes certifiées, un Property `Appartement / Dakar / Almadies` puis un brouillon intitulé `Appartement P05 aux Almadies`. Le navigateur a atteint l’étape Photos sans erreur console.

Aucune photo immobilière réelle n’étant disponible dans le workspace, aucun média artificiel n’a été injecté pour fabriquer une preuve. La démonstration n’a donc pas revendiqué Upload, Preview ou Submit comme preuves terminales.

## Capture

`docs/product/sprints/P05-CREATE-LISTING.png` représente l’assistant réellement servi sous session IAM.
