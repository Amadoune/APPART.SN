# Media Item Lifecycle Workflow Analysis

Le workflow 4.6A formalise uniquement le statut d'un média. Il est une fonction pure de `(state, action)` et ne dépend ni de `MediaCollection`, ni d'un média principal, ni de l'ordre, du checksum, de Property ou d'une source externe.

La création demeure hors workflow et produit explicitement l'état initial `Active`. Les deux mutations exécutables sont terminales : `Remove` conduit à `Removed`, `Archive` conduit à `Archived`.
