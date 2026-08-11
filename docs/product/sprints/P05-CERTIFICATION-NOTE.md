# P05 — Certification Note

## Capacités certifiées

P05 compose correctement les capacités certifiées nécessaires au parcours propriétaire : Login, Workspace, Property Authoring, Listing Draft, Media Authoring, prévisualisation et Submit.

L’étape Photos est réellement reliée au Runtime Media owner-scoped. La prévisualisation consomme exclusivement les données saisies et les médias acceptés. Le Submit appelle le pipeline Listing certifié et respecte sa frontière terminale `Submitted`.

Aucune décision IAM, Media, Property ou Listing Lifecycle n’est dupliquée dans l’interface. Aucun contournement, SQL direct, mock ou publication implicite n’est introduit.

## Prérequis de démonstration

L’environnement local ne contenait pas de photo immobilière réelle disponible et autorisée pour le rejeu terminal. Cette condition empêche uniquement la démonstration complète `Upload → Preview → Submit` avec un nouveau média.

Elle ne remet pas en cause l’implémentation, le branchement ou la certification de Media Authoring, Property Authoring, IAM, Listing Authoring et du parcours P05. La preuve pourra être rejouée sans changement logiciel dès qu’un média immobilier réel sera fourni à l’environnement de démonstration.

## Frontière produit

P05 s’arrête à `Submitted`. `BeginReview`, `ApproveAndPublish`, la publication, Search, la Projection publique et la fiche publique appartiennent à une autorité et à un parcours ultérieurs.

## Verdict

**GO PROPOSÉ — APPART.SN PRODUCT SPRINT P05 — FINAL CERTIFICATION**

Le prérequis de démonstration restant est explicitement non bloquant pour la certification des capacités produit.
