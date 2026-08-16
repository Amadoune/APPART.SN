# Version Model

Le writer exige une version positive et applique une monotonie stricte par ListingId : supérieure remplace, égale converge ou diverge, inférieure est obsolète.

Il n'attribue pas la version. Aucun ledger, sequence allocator ou règle `première décision = 1` n'est exposé par le contrat Application.

Le modèle candidat — première décision 1, changement canonique +1, replay inchangé — est compatible avec le writer mais **non certifié** par les autorités existantes. Il ne peut être adopté dans ce chantier après le fail-fast Rank.

La version Search du watermark reste donc non matérialisable de manière normative.

## Completion 01

Constat historique fermé. `FINAL-VERSION-MODEL.md` certifie première version `1`, replay inchangé sans incrément, puis `current+1` pour un ensemble de révisions dominant. Le Writer conserve l'arbitrage final.
