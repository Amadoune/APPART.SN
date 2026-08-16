# Final version model

- aucune décision + source ready : version 1 / Applied;
- même sourceRevision et payload : AlreadyApplied;
- source nouvelle stable : version courante + 1 / Applied;
- source dominée : RejectedObsolete;
- même niveau logique, payload différent : Divergent;
- concurrence incomparable : retry après relecture.

Le writer conserve l'entier positif monotone et le verrou par collection. Son support V2 compare le sourceRevision inclus dans JSONB; aucune colonne nouvelle.
