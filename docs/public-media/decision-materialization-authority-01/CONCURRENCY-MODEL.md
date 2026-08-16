# Concurrency model

Deux workers sur une collection sont sérialisés par advisory lock et `FOR UPDATE`. Le writer monotone arbitre replay, stale et divergence.

Add/remove/reorder/primary ou delivery change pendant un catch-up exige une révision source complète et une relecture. La partie delivery de cette révision est le blocage actuel.
