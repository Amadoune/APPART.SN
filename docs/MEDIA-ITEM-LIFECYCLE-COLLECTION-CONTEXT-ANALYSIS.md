# Media Item Lifecycle Collection Transition Context Analysis

## Objet

Le Sprint **4.6C-R1** définit exclusivement le contrat V1 transmis après une décision de `MediaCollection`. Il ne décide ni si le média est principal, ni quel média doit le remplacer.

Le contexte porte :

- l'identité de la collection et du média concerné ;
- la version attendue du journal Media Item Lifecycle ;
- la version de collection ayant produit la décision ;
- l'acteur et l'instant UTC explicites ;
- la décision propriétaire `NotPrimary` ou `ReplacementSelected` ;
- un checksum SHA-256 déterministe couvrant tous ces champs.

## Frontière de responsabilité

`MediaCollection` reste l'unique propriétaire du statut principal, de l'éligibilité du remplaçant, de l'ordre et des invariants de collection. Le contexte est une preuve transportée, pas une demande de décision.

Le futur orchestrateur devra accepter le contexte tel quel. Il lui sera interdit de lire une collection, d'inférer le statut principal, de choisir un remplaçant ou de transformer une absence de remplaçant en décision implicite.

## Hors périmètre

Aucune persistance, migration, source, adaptation Runtime, orchestration, publication, Inbox, Outbox ou exposition HTTP n'est introduite.
