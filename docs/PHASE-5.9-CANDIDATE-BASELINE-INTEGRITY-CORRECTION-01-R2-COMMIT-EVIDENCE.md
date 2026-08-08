# R2 Commit Evidence

Filiation obligatoire :

`1337e225…` → baseline R1 historique → commits Build/CI et preuve NO GO historiques → correction d'intégrité bornée → baseline R2.

Le SHA R2 est défini comme `git rev-parse phase-5.9-baseline-candidate-r2^{commit}`. Il est rapporté après commit, son inscription dans son propre arbre étant auto-référentiellement impossible.

Message attendu : `fix(baseline): materialize phase 5.9 integrity correction r2`.
