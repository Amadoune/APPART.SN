# Official Remote and External CI Authority Resolution 01 — Execution Authority Model

La chaîne recevable doit être établie par une décision externe traçable :

1. l'autorité projet désigne un repository et son owner ;
2. l'owner atteste l'URL, l'activation Actions et les politiques applicables ;
3. un principal explicitement autorisé vérifie ou publie sans réécriture le tag annoté R5 ;
4. le tag distant résout vers `9801d9ed30ea3a5fa412708cd022d16bc84e472c` ;
5. un opérateur autorisé déclenche le workflow R5 exact ;
6. la plateforme produit run ID, run attempt, `GITHUB_SHA`, logs et artefact ;
7. un custodian désigné conserve les preuves au-delà de la rétention temporaire ;
8. un second opérateur ou environnement indépendant reproduit le Release Candidate.

Aucune de ces autorités ne peut être déduite de la seule possession du workspace local.
