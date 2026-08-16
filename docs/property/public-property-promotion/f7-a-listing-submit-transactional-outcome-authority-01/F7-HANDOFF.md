# Handoff vers F7

Après GO de l’implémentation F7-A, F7 reprend exactement au scénario interrompu :

1. Promotion `Applied` ;
2. Workflow retourne un closed failure ;
3. transaction Listing rollback ;
4. Property et ledger Promotion restent durables ;
5. Aggregate reste Draft et Workflow pré-Submit ;
6. retry Promotion → `AlreadyApplied` ;
7. retry Listing → succès déterministe.

F7 poursuit ensuite seulement les preuves restantes : Aggregate compatible/incompatible, Geography NotFound/Merged/NotAddressable/indisponible, contenu exact du ledger et recertification terminale.

RC2 Iteration 11 demeure fermée jusqu’au verdict F7.
