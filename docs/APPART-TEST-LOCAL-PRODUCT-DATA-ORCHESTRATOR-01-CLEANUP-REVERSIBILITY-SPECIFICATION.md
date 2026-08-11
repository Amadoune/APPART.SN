# APPART.TEST LOCAL PRODUCT DATA ORCHESTRATOR 01 — Cleanup / Reversibility Specification

## Exigence

Le nettoyage doit cibler exclusivement un identifiant et un marqueur de développement connus, exécuter les transitions de retrait ou d'archivage, propager la décision et faire converger la projection sans `DELETE`, reset global ou SQL ad hoc.

## Statut

`BLOCKED`

Aucune donnée n'a été créée par ce jalon. La symétrie ne peut être démontrée tant que la création initiale et l'activation conforme d'une première génération ne sont pas possibles.

## Intégrité

Aucune table, génération ou projection n'a été modifiée. Aucun nettoyage destructif n'a été tenté.
